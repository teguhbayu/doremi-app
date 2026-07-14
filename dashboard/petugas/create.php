<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaPetugas'] ?? '');
    $email = trim($_POST['emailPetugas'] ?? '');
    $no = (string) preg_replace('/\D+/', '', trim($_POST['noPetugas'] ?? ''));
    $jabatan = trim($_POST['jabatanPetugas'] ?? '');
    $password = trim($_POST['passwordPetugas'] ?? '');
    $confirmPassword = trim($_POST['confirmPasswordPetugas'] ?? '');

    $petugasSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100))
        ,
        v::key('email', v::email()->length(3, 100))
        ,
        v::key('no', v::digit()->length(10, 16))
        ,
        v::key('jabatan', v::alpha()->in(["PENGURUS", "SIGAP", "SERVANDA", "MAINTENANCE"]))
        ,
        v::key('password', v::length(8, 100))
        ,
        v::key('confirmPassword', v::length(8, 100))
    );

    $postData = [
        'nama' => $nama,
        'email' => $email,
        'no' => $no,
        'jabatan' => $jabatan,
        'password' => $password,
        'confirmPassword' => $confirmPassword,
    ];


    if (!$petugasSchema->validate($postData)) {
        $_SESSION['form_data'] = $postData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Petugas Baru tidak Valid!');
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['form_data'] = $postData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Password Tidak Cocok!');
        exit;
    }

    $activeCheckStmt = mysqli_prepare(
        $db,
        "SELECT PetugasID, Email, NoHP
         FROM petugas
         WHERE IsDeleted = 0 AND (Email = ? OR NoHP = ?)
         LIMIT 1"
    );
    mysqli_stmt_bind_param($activeCheckStmt, 'ss', $email, $no);
    mysqli_stmt_execute($activeCheckStmt);
    $activeCheckResult = mysqli_stmt_get_result($activeCheckStmt);
    $activePetugas = mysqli_fetch_assoc($activeCheckResult);
    mysqli_stmt_close($activeCheckStmt);

    if ($activePetugas) {
        $_SESSION['form_data'] = $postData;

        if (($activePetugas['Email'] ?? '') === $email) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email petugas sudah terdaftar!');
            exit;
        }

        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=No. HP petugas sudah terdaftar!');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $deletedCheckStmt = mysqli_prepare(
        $db,
        "SELECT PetugasID, Email, NoHP
         FROM petugas
         WHERE IsDeleted = 1 AND (Email = ? OR NoHP = ?)
         ORDER BY PetugasID ASC"
    );
    mysqli_stmt_bind_param($deletedCheckStmt, 'ss', $email, $no);
    mysqli_stmt_execute($deletedCheckStmt);
    $deletedCheckResult = mysqli_stmt_get_result($deletedCheckStmt);
    $deletedPetugasRows = mysqli_fetch_all($deletedCheckResult, MYSQLI_ASSOC);
    mysqli_stmt_close($deletedCheckStmt);

    $restoredDeletedPetugasId = null;
    if ($deletedPetugasRows) {
        $candidateIds = array_values(array_unique(array_map(
            static fn(array $row): int => (int) $row['PetugasID'],
            $deletedPetugasRows
        )));

        if (count($candidateIds) > 1) {
            $_SESSION['form_data'] = $postData;
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email atau No. HP terkait dengan data petugas terhapus yang berbeda. Gunakan data lain atau edit data lama.');
            exit;
        }

        $restoredDeletedPetugasId = $candidateIds[0];
    }

    if ($restoredDeletedPetugasId !== null) {
        $restoreStmt = mysqli_prepare(
            $db,
            "UPDATE petugas
             SET NamaPetugas = ?, Email = ?, Password = ?, Jabatan = ?, NoHP = ?, IsDeleted = 0, UpdatedAt = NOW()
             WHERE PetugasID = ?"
        );
        mysqli_stmt_bind_param($restoreStmt, 'sssssi', $nama, $email, $hashedPassword, $jabatan, $no, $restoredDeletedPetugasId);

        if (!mysqli_stmt_execute($restoreStmt)) {
            $_SESSION['form_data'] = $postData;
            mysqli_stmt_close($restoreStmt);
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Gagal memulihkan data petugas yang pernah dihapus!');
            exit;
        }

        mysqli_stmt_close($restoreStmt);
        unset($_SESSION['form_data']);

        header("Location: " . '/doremi-app/dashboard/petugas/' . '?status=success&message=Petugas berhasil ditambahkan kembali dari data yang pernah dihapus!');
        exit;
    }

    $stmt = mysqli_prepare($db, "INSERT INTO petugas (NamaPetugas, Email, Password, Jabatan, NoHP) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $hashedPassword, $jabatan, $no);

    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['form_data'] = $postData;
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menambahkan petugas!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);
    unset($_SESSION['form_data']);

    header("Location: " . '/doremi-app/dashboard/petugas/' . '?status=success&message=Petugas Berhasil Ditambahkan!');
    exit;
}

// Retrieve form data from session if it exists (after a failed submission)
$formData = $_SESSION['form_data'] ?? [
    'nama' => '',
    'email' => '',
    'no' => '',
    'jabatan' => 'Pilih Salah Satu',
    'password' => '',
    'confirmPassword' => ''
];
unset($_SESSION['form_data']); // Clear the data after retrieving

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Buat akun petugas baru lengkap dengan peran, kontak, dan kredensial masuk sistem.">
                Tambah Petugas
            </h1>
            <div class="page-toolbar" data-note="Form akun petugas baru">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm" x-data='<?= json_encode($formData, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                <div class="mb-3">
                    <label for="namaPetugas" class="form-label">Nama Petugas</label>
                    <input type="text" name="namaPetugas" x-model="nama" class="form-control" id="namaPetugas" required>
                </div>
                <div class="mb-3">
                    <label for="emailPetugas" class="form-label">Email Petugas</label>
                    <input type="email" name="emailPetugas" x-model="email" class="form-control" id="emailPetugas" required>
                </div>
                <div class="mb-3">
                    <label for="noPetugas" class="form-label">No. HP</label>
                    <input type="text" name="noPetugas" x-model="no" class="form-control" id="noPetugas"
                        inputmode="numeric" pattern="[0-9]{10,16}" maxlength="16" required>
                </div>
                <div class="mb-3">
                    <label for="jabatanPetugas" class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatanPetugas" x-model="jabatan" id="jabatanPetugas" required>
                        <option value="Pilih Salah Satu" disabled>Pilih Salah Satu</option>
                        <option value="PENGURUS">PENGURUS</option>
                        <option value="SIGAP">SIGAP</option>
                        <option value="SERVANDA">SERVANDA</option>
                        <option value="MAINTENANCE">MAINTENANCE</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="passwordPetugas" class="form-label">Password</label>
                    <input type="password" name="passwordPetugas" x-model="password" class="form-control" id="passwordPetugas" minlength="8" autocomplete="new-password" required>
                    <span class="form-hint">Saran: pakai minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</span>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPetugas" class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirmPasswordPetugas" x-model="confirmPassword" class="form-control" minlength="8" autocomplete="new-password"
                        id="confirmPasswordPetugas" required>
                </div>
                <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-2">
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
</body>

</html>
