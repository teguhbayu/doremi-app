<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';
require 'helpers.php';

$kamarQuery = mysqli_query(
    $db,
    "SELECT
        k.KamarID,
        k.NomorKamar,
        k.Lantai,
        k.KapasitasPenghuni,
        COUNT(p.PenghuniID) AS JumlahPenghuniAktual
    FROM kamar k
    LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0
    WHERE k.IsDeleted = 0
    GROUP BY k.KamarID, k.NomorKamar, k.Lantai, k.KapasitasPenghuni
    ORDER BY k.NomorKamar ASC"
);
$kamars = mysqli_fetch_all($kamarQuery, MYSQLI_ASSOC);
$kamarMap = [];
foreach ($kamars as $kamar) {
    $kamarMap[(int) $kamar['KamarID']] = $kamar;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaPenghuni'] ?? '');
    $nim = penghuni_normalize_nim($_POST['nimPenghuni'] ?? '');
    $email = penghuni_normalize_email($_POST['emailPenghuni'] ?? '');
    $no = penghuni_normalize_phone($_POST['noPenghuni'] ?? '');
    $jk = trim($_POST['jkPenghuni'] ?? '');
    $kamarId = trim($_POST['kamarPenghuni'] ?? '');
    $alamat = trim($_POST['alamatPenghuni'] ?? '');
    $password = trim($_POST['passwordPenghuni'] ?? '');
    $confirmPassword = trim($_POST['confirmPasswordPenghuni'] ?? '');
    $formData = [
        'nama' => $nama,
        'nim' => $nim,
        'email' => $email,
        'no' => $no,
        'jk' => $jk,
        'kamarId' => $kamarId,
        'alamat' => $alamat,
    ];

    $penghuniSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100)),
        v::key('nim', v::stringType()),
        v::key('email', v::email()->length(3, 100)),
        v::key('no', v::stringType()),
        v::key('jk', v::in(['L', 'P'])),
        v::key('kamarId', v::numericVal()),
        v::key('alamat', v::stringType()->length(3, 255)),
        v::key('password', v::length(8, 100)),
        v::key('confirmPassword', v::length(8, 100))
    );

    $postData = [
        'nama' => $nama,
        'nim' => $nim,
        'email' => $email,
        'no' => $no,
        'jk' => $jk,
        'kamarId' => $kamarId,
        'alamat' => $alamat,
        'password' => $password,
        'confirmPassword' => $confirmPassword,
    ];

    if (!$penghuniSchema->validate($postData)) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data Penghuni tidak Valid!');
        exit;
    }

    if (!penghuni_is_valid_nim($nim)) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode(penghuni_nim_validation_message()));
        exit;
    }

    if (!penghuni_is_valid_phone($no)) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=No. HP harus 10-16 digit angka yang valid!');
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Password Tidak Cocok!');
        exit;
    }

    $selectedKamar = $kamarMap[(int) $kamarId] ?? null;
    if (!$selectedKamar) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Kamar yang dipilih tidak ditemukan!');
        exit;
    }

    $activeMatches = penghuni_find_identity_matches($db, $nim, $email, $no, 0);
    $activePenghuni = $activeMatches[0] ?? null;

    if ($activePenghuni) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode(penghuni_duplicate_identity_message($activePenghuni, $nim, $email, $no)));
        exit;
    }

    $roomValidationMessage = penghuni_validate_room_assignment($db, (int) $kamarId, $jk);
    if ($roomValidationMessage !== null) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=' . urlencode($roomValidationMessage));
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $deletedPenghuniRows = penghuni_find_identity_matches($db, $nim, $email, $no, 1);

    $restoredDeletedPenghuniId = null;
    if ($deletedPenghuniRows) {
        $candidateIds = array_values(array_unique(array_map(
            static fn(array $row): int => (int) $row['PenghuniID'],
            $deletedPenghuniRows
        )));

        if (count($candidateIds) > 1) {
            $_SESSION['form_data'] = $formData;
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data NIM, email, atau No. HP terkait dengan data penghuni terhapus yang berbeda. Gunakan data lain atau edit data lama.');
            exit;
        }

        $restoredDeletedPenghuniId = $candidateIds[0];
    }

    if ($restoredDeletedPenghuniId !== null) {
        $restoreStmt = mysqli_prepare(
            $db,
            "UPDATE penghuni
             SET KamarID = ?, NamaPenghuni = ?, Nim = ?, JenisKelamin = ?, NoHP = ?, Email = ?, Password = ?, Alamat = ?, IsDeleted = 0, UpdateAt = NOW()
             WHERE PenghuniID = ?"
        );
        mysqli_stmt_bind_param($restoreStmt, 'isssssssi', $kamarId, $nama, $nim, $jk, $no, $email, $hashedPassword, $alamat, $restoredDeletedPenghuniId);

        if (!mysqli_stmt_execute($restoreStmt)) {
            $_SESSION['form_data'] = $formData;
            mysqli_stmt_close($restoreStmt);
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Gagal memulihkan data penghuni yang pernah dihapus!');
            exit;
        }

        mysqli_stmt_close($restoreStmt);
        unset($_SESSION['form_data']);

        header("Location: " . '/doremi-app/dashboard/penghuni/' . '?status=success&message=Penghuni berhasil ditambahkan kembali dari data yang pernah dihapus!');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "INSERT INTO penghuni (KamarID, NamaPenghuni, Nim, JenisKelamin, NoHP, Email, Password, Alamat, UpdateAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issssssss', $kamarId, $nama, $nim, $jk, $no, $email, $hashedPassword, $alamat, $now);

    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['form_data'] = $formData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menyimpan data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);
    unset($_SESSION['form_data']);

    header("Location: " . '/doremi-app/dashboard/penghuni/' . '?status=success&message=Penghuni Berhasil Ditambahkan!');
    exit;
}

$formData = $_SESSION['form_data'] ?? [
    'nama' => '',
    'nim' => '',
    'email' => '',
    'no' => '',
    'jk' => '',
    'kamarId' => '',
    'alamat' => '',
];
unset($_SESSION['form_data']);

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Daftarkan penghuni baru dengan identitas, penempatan kamar, dan akses masuk yang siap digunakan.">
                Tambah Penghuni
            </h1>
            <div class="page-toolbar" data-note="Pastikan kamar sesuai kapasitas dan jenis kelamin penghuni">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="form-shell">
                <div class="mb-3">
                    <label for="nimPenghuni" class="form-label">NIM</label>
                    <input type="text" name="nimPenghuni" class="form-control" id="nimPenghuni"
                        minlength="<?= penghuni_nim_min_length() ?>" maxlength="<?= penghuni_nim_max_length() ?>"
                        value="<?= htmlspecialchars($formData['nim']) ?>" required>
                    <span class="form-hint">Gunakan <?= penghuni_nim_min_length() ?>-<?= penghuni_nim_max_length() ?> karakter tanpa spasi. Contoh: 0920250045.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPenghuni" class="form-label">Nama Penghuni</label>
                    <input type="text" name="namaPenghuni" class="form-control" id="namaPenghuni" value="<?= htmlspecialchars($formData['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="emailPenghuni" class="form-label">Email</label>
                    <input type="email" name="emailPenghuni" class="form-control" id="emailPenghuni" value="<?= htmlspecialchars($formData['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="noPenghuni" class="form-label">No. HP</label>
                    <input type="text" name="noPenghuni" class="form-control" id="noPenghuni" inputmode="numeric"
                        pattern="[0-9]{10,16}" maxlength="16" value="<?= htmlspecialchars($formData['no']) ?>" required>
                    <span class="form-hint">Masukkan 10-16 digit angka aktif tanpa spasi atau simbol.</span>
                </div>
                <div class="mb-3">
                    <label for="jkPenghuni" class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jkPenghuni" id="jkPenghuni" required>
                        <option value="" <?= !in_array($formData['jk'], ['L', 'P'], true) ? 'selected' : '' ?> disabled>Pilih Salah Satu</option>
                        <option value="L" <?= $formData['jk'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $formData['jk'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kamarPenghuni" class="form-label">Kamar</label>
                    <select class="form-select" name="kamarPenghuni" id="kamarPenghuni" required>
                        <option value="" <?= $formData['kamarId'] === '' ? 'selected' : '' ?> disabled>Pilih Kamar</option>
                        <?php foreach ($kamars as $kamar): ?>
                            <option value="<?= $kamar['KamarID'] ?>" <?= (string) $formData['kamarId'] === (string) $kamar['KamarID'] ? 'selected' : '' ?>>
                                <?= $kamar['NomorKamar'] ?> - Lantai <?= $kamar['Lantai'] ?>
                                (<?= $kamar['JumlahPenghuniAktual'] ?>/<?= $kamar['KapasitasPenghuni'] ?> terisi)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="alamatPenghuni" class="form-label">Alamat</label>
                    <textarea name="alamatPenghuni" class="form-control" id="alamatPenghuni" rows="3"
                        required><?= htmlspecialchars($formData['alamat']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="passwordPenghuni" class="form-label">Password</label>
                    <input type="password" name="passwordPenghuni" class="form-control" id="passwordPenghuni" minlength="8" autocomplete="new-password" required>
                    <span class="form-hint">Saran: pakai minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</span>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPenghuni" class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirmPasswordPenghuni" class="form-control" minlength="8" autocomplete="new-password"
                        id="confirmPasswordPenghuni" required>
                </div>
                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
                    <button type="submit"
                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-2 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                        <span>
                            Simpan
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nimInput = document.getElementById('nimPenghuni');

            if (!nimInput) {
                return;
            }

            const syncNimField = () => {
                nimInput.value = nimInput.value.replace(/\s+/g, '').toUpperCase();
            };

            nimInput.addEventListener('input', syncNimField);
            syncNimField();
        });
    </script>
</body>

</html>
