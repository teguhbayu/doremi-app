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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/penghuni/");
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM penghuni WHERE PenghuniID = ? AND IsDeleted = 0 LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$penghuni = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$penghuni) {
    header("Location: /doremi-app/dashboard/penghuni/");
    exit;
}

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

    $isChangingPassword = $password !== '' || $confirmPassword !== '';

    $baseSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100)),
        v::key('nim', v::stringType()),
        v::key('email', v::email()->length(3, 100)),
        v::key('no', v::stringType()),
        v::key('jk', v::in(['L', 'P'])),
        v::key('kamarId', v::numericVal()),
        v::key('alamat', v::stringType()->length(3, 255)),
        v::key('password', v::optional(v::length(8, 100))),
        v::key('confirmPassword', v::optional(v::length(8, 100)))
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

    if (!$baseSchema->validate($postData)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Data Penghuni Tidak Valid!');
        exit;
    }

    if (!penghuni_is_valid_nim($nim)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode(penghuni_nim_validation_message()));
        exit;
    }

    if (!penghuni_is_valid_phone($no)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=No. HP harus 10-16 digit angka yang valid!');
        exit;
    }

    if ($isChangingPassword && $password !== $confirmPassword) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Password Tidak Cocok!');
        exit;
    }

    $selectedKamar = $kamarMap[(int) $kamarId] ?? null;
    if (!$selectedKamar) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Kamar yang dipilih tidak ditemukan!');
        exit;
    }

    $duplicateMatches = penghuni_find_identity_matches($db, $nim, $email, $no, 0, (int) $id);
    $duplicatePenghuni = $duplicateMatches[0] ?? null;

    if ($duplicatePenghuni) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode(penghuni_duplicate_identity_message($duplicatePenghuni, $nim, $email, $no)));
        exit;
    }

    $roomValidationMessage = penghuni_validate_room_assignment($db, (int) $kamarId, $jk, (int) $id);
    if ($roomValidationMessage !== null) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode($roomValidationMessage));
        exit;
    }

    $now = date('Y-m-d H:i:s');

    if ($isChangingPassword) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($db, "UPDATE penghuni SET KamarID = ?, NamaPenghuni = ?, Nim = ?, JenisKelamin = ?, NoHP = ?, Email = ?, Password = ?, Alamat = ?, UpdateAt = ? WHERE PenghuniID = ?");
        mysqli_stmt_bind_param($stmt, 'issssssssi', $kamarId, $nama, $nim, $jk, $no, $email, $hashedPassword, $alamat, $now, $id);
    } else {
        $stmt = mysqli_prepare($db, "UPDATE penghuni SET KamarID = ?, NamaPenghuni = ?, Nim = ?, JenisKelamin = ?, NoHP = ?, Email = ?, Alamat = ?, UpdateAt = ? WHERE PenghuniID = ?");
        mysqli_stmt_bind_param($stmt, 'isssssssi', $kamarId, $nama, $nim, $jk, $no, $email, $alamat, $now, $id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Terjadi Kesalahan saat mengupdate data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: /doremi-app/dashboard/penghuni/?status=success&message=Penghuni Berhasil Diupdate!");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Perbarui profil penghuni, kamar, dan akses login sambil menjaga validasi kamar tetap konsisten.">
                Edit Penghuni
            </h1>
            <div class="page-toolbar" data-note="Kosongkan password jika hanya ingin mengubah profil penghuni">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                <div class="mb-3">
                    <label for="nimPenghuni" class="form-label">NIM</label>
                    <input type="text" name="nimPenghuni" class="form-control" id="nimPenghuni"
                        minlength="<?= penghuni_nim_min_length() ?>" maxlength="<?= penghuni_nim_max_length() ?>"
                        value="<?= htmlspecialchars($penghuni['Nim']) ?>" required>
                    <span class="form-hint">Gunakan <?= penghuni_nim_min_length() ?>-<?= penghuni_nim_max_length() ?> karakter tanpa spasi agar validasi NIM sesuai batas database.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPenghuni" class="form-label">Nama Penghuni</label>
                    <input type="text" name="namaPenghuni" class="form-control" id="namaPenghuni"
                        value="<?= htmlspecialchars($penghuni['NamaPenghuni']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="emailPenghuni" class="form-label">Email</label>
                    <input type="email" name="emailPenghuni" class="form-control" id="emailPenghuni"
                        value="<?= htmlspecialchars($penghuni['Email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="noPenghuni" class="form-label">No. HP</label>
                    <input type="text" name="noPenghuni" class="form-control" id="noPenghuni" inputmode="numeric"
                        pattern="[0-9]{10,16}" maxlength="16"
                        value="<?= htmlspecialchars($penghuni['NoHP']) ?>" required>
                    <span class="form-hint">Masukkan 10-16 digit angka aktif tanpa spasi atau simbol.</span>
                </div>
                <div class="mb-3">
                    <label for="jkPenghuni" class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jkPenghuni" id="jkPenghuni" required>
                        <option value="L" <?= $penghuni['JenisKelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $penghuni['JenisKelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kamarPenghuni" class="form-label">Kamar</label>
                    <select class="form-select" name="kamarPenghuni" id="kamarPenghuni" required>
                        <?php foreach ($kamars as $kamar): ?>
                            <option value="<?= $kamar['KamarID'] ?>" <?= $penghuni['KamarID'] == $kamar['KamarID'] ? 'selected' : '' ?>>
                                <?= $kamar['NomorKamar'] ?> - Lantai <?= $kamar['Lantai'] ?>
                                (<?= $kamar['JumlahPenghuniAktual'] ?>/<?= $kamar['KapasitasPenghuni'] ?> terisi)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="alamatPenghuni" class="form-label">Alamat</label>
                    <textarea name="alamatPenghuni" class="form-control" id="alamatPenghuni" rows="3" required><?= htmlspecialchars($penghuni['Alamat']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="passwordPenghuni" class="form-label">Password Baru <span
                            class="tw:text-gray-400 tw:text-sm">(kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="passwordPenghuni" class="form-control" id="passwordPenghuni" minlength="8" autocomplete="new-password">
                    <span class="form-hint">Saran: pakai minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</span>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPenghuni" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirmPasswordPenghuni" class="form-control" minlength="8" autocomplete="new-password"
                        id="confirmPasswordPenghuni">
                </div>
                <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-2">
                    <button type="submit"
                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-2 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>

</html>
