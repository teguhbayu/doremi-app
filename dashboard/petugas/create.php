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
    $no = trim($_POST['noPetugas'] ?? '');
    $jabatan = trim($_POST['jabatanPetugas'] ?? '');
    $password = trim($_POST['passwordPetugas'] ?? '');
    $confirmPassword = trim($_POST['confirmPasswordPetugas'] ?? '');

    $petugasSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100))
        ,
        v::key('email', v::email()->length(3, 100))
        ,
        v::key('no', v::digit()->length(10, 15))
        ,
        v::key('jabatan', v::alpha()->in(["PENGURUS", "SIGAP", "SERVANDA", "MAINTENANCE"]))
        ,
        v::key('password', v::length(5, 100))
        ,
        v::key('confirmPassword', v::length(5, 100))
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
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Petugas Baru tidak Valid!');
        exit;
    }

    if ($password !== $confirmPassword) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Password Tidak Cocok!');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($db, "INSERT INTO petugas (NamaPetugas, Email, Password, Jabatan, NoHP) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $email, $hashedPassword, $jabatan, $no);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=success&message=Terjadi Kesalahan!!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: " . '/doremi-app/dashboard/petugas/' . '?status=success&message=Petugas Berhasil Ditambahkan!');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <h1 class="tw:font-bold tw:mb-5 tw:text-4xl tw:text-black">
                Tambah Petugas
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="namaPetugas" class="form-label">Nama Petugas</label>
                    <input type="text" name="namaPetugas" class="form-control" id="namaPetugas">
                </div>
                <div class="mb-3">
                    <label for="emailPetugas" class="form-label">Email Petugas</label>
                    <input type="email" name="emailPetugas" class="form-control" id="emailPetugas">
                </div>
                <div class="mb-3">
                    <label for="noPetugas" class="form-label">No. HP</label>
                    <input type="number" name="noPetugas" class="form-control" id="noPetugas">
                </div>
                <div class="mb-3">
                    <label for="jabatanPetugas" class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatanPetugas" id="jabatanPetugas">
                        <option selected>Pilih Salah Satu</option>
                        <option value="PENGURUS">PENGURUS</option>
                        <option value="SIGAP">SIGAP</option>
                        <option value="SERVANDA">SERVANDA</option>
                        <option value="MAINTENANCE">MAINTENANCE</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="passwordPetugas" class="form-label">Password</label>
                    <input type="password" name="passwordPetugas" class="form-control" id="passwordPetugas">
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPetugas" class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirmPasswordPetugas" class="form-control"
                        id="confirmPasswordPetugas">
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
</body>

</html>