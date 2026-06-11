<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/penghuni/");
    exit;
}

// Fetch resident data
$stmt = mysqli_prepare($db, "SELECT * FROM penghuni WHERE PenghuniID = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$penghuni = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$penghuni) {
    header("Location: /doremi-app/dashboard/penghuni/");
    exit;
}

// Fetch active rooms
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

    $isChangingPassword = $password !== '' || $confirmPassword !== '';

    $baseSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100)),
        v::key('nim', v::stringType()->length(5, 15)),
        v::key('email', v::email()->length(3, 100)),
        v::key('no', v::digit()->length(10, 15)),
        v::key('jk', v::in(['L', 'P'])),
        v::key('kamarId', v::numericVal()),
        v::key('alamat', v::stringType()->length(3, 255)),
        v::key('password', v::optional(v::length(5, 100))),
        v::key('confirmPassword', v::optional(v::length(5, 100)))
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

    if ($isChangingPassword && $password !== $confirmPassword) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Password Tidak Cocok!');
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

<body class="tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:ml-75 tw:grow">
        <div class="tw:pt-5 tw:px-5 tw:flex-1 tw:w-full">
            <h1 class="tw:font-bold tw:mb-5 tw:text-4xl tw:text-black">
                Edit Penghuni
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="penghuniID" class="form-label">ID Penghuni</label>
                    <input type="text" class="form-control" id="penghuniID"
                        value="<?= htmlspecialchars($penghuni['PenghuniID']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="nimPenghuni" class="form-label">NIM</label>
                    <input type="text" name="nimPenghuni" class="form-control" id="nimPenghuni"
                        value="<?= htmlspecialchars($penghuni['Nim']) ?>" required>
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
                    <input type="number" name="noPenghuni" class="form-control" id="noPenghuni"
                        value="<?= htmlspecialchars($penghuni['NoHP']) ?>" required>
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
                                <?= $kamar['NomorKamar'] ?>
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
                    <input type="password" name="passwordPenghuni" class="form-control" id="passwordPenghuni">
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPenghuni" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirmPasswordPenghuni" class="form-control"
                        id="confirmPasswordPenghuni">
                </div>
                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
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
