<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$kamarQuery = mysqli_query($db, "SELECT KamarID, NomorKamar FROM kamar WHERE IsDeleted = 0 ORDER BY NomorKamar ASC");
$kamars = mysqli_fetch_all($kamarQuery, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaPenghuni'] ?? '');
    $nim = trim($_POST['nimPenghuni'] ?? '');
    $email = trim($_POST['emailPenghuni'] ?? '');
    $no = trim($_POST['noPenghuni'] ?? '');
    $jk = trim($_POST['jkPenghuni'] ?? '');
    $kamarId = trim($_POST['kamarPenghuni'] ?? '');
    $alamat = trim($_POST['alamatPenghuni'] ?? '');
    $password = trim($_POST['passwordPenghuni'] ?? '');
    $confirmPassword = trim($_POST['confirmPasswordPenghuni'] ?? '');

    $penghuniSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100)),
        v::key('nim', v::stringType()->length(5, 15)),
        v::key('email', v::email()->length(3, 100)),
        v::key('no', v::digit()->length(10, 15)),
        v::key('jk', v::in(['L', 'P'])),
        v::key('kamarId', v::numericVal()),
        v::key('alamat', v::stringType()->length(1, 255)),
        v::key('password', v::length(5, 100)),
        v::key('confirmPassword', v::length(5, 100))
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
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data Penghuni tidak Valid!');
        exit;
    }

    if ($password !== $confirmPassword) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Password Tidak Cocok!');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "INSERT INTO penghuni (KamarID, NamaPenghuni, Nim, JenisKelamin, NoHP, Email, Password, Alamat, UpdateAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issssssss', $kamarId, $nama, $nim, $jk, $no, $email, $hashedPassword, $alamat, $now);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menyimpan data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: " . '/doremi-app/dashboard/penghuni/' . '?status=success&message=Penghuni Berhasil Ditambahkan!');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:ml-75 tw:grow">
        <div class="tw:pt-5 tw:px-5 tw:flex-1 tw:w-full">
            <h1 class="tw:font-bold tw:mb-5 tw:text-4xl tw:text-black">
                Tambah Penghuni
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="nimPenghuni" class="form-label">NIM</label>
                    <input type="text" name="nimPenghuni" class="form-control" id="nimPenghuni" required>
                </div>
                <div class="mb-3">
                    <label for="namaPenghuni" class="form-label">Nama Penghuni</label>
                    <input type="text" name="namaPenghuni" class="form-control" id="namaPenghuni" required>
                </div>
                <div class="mb-3">
                    <label for="emailPenghuni" class="form-label">Email</label>
                    <input type="email" name="emailPenghuni" class="form-control" id="emailPenghuni" required>
                </div>
                <div class="mb-3">
                    <label for="noPenghuni" class="form-label">No. HP</label>
                    <input type="number" name="noPenghuni" class="form-control" id="noPenghuni" required>
                </div>
                <div class="mb-3">
                    <label for="jkPenghuni" class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jkPenghuni" id="jkPenghuni" required>
                        <option selected disabled>Pilih Salah Satu</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kamarPenghuni" class="form-label">Kamar</label>
                    <select class="form-select" name="kamarPenghuni" id="kamarPenghuni" required>
                        <option selected disabled>Pilih Kamar</option>
                        <?php foreach ($kamars as $kamar): ?>
                            <option value="<?= $kamar['KamarID'] ?>"><?= $kamar['NomorKamar'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="alamatPenghuni" class="form-label">Alamat</label>
                    <textarea name="alamatPenghuni" class="form-control" id="alamatPenghuni" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="passwordPenghuni" class="form-label">Password</label>
                    <input type="password" name="passwordPenghuni" class="form-control" id="passwordPenghuni" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPenghuni" class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirmPasswordPenghuni" class="form-control"
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
</body>

</html>
