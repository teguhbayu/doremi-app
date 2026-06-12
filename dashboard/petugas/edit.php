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
    header("Location: /doremi-app/dashboard/petugas/");
    exit;
}

$stmt = mysqli_prepare($db, "SELECT PetugasID, NamaPetugas, Email, Jabatan, NoHP FROM petugas WHERE PetugasID = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$petugas = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$petugas) {
    header("Location: /doremi-app/dashboard/petugas/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaPetugas'] ?? '');
    $email = trim($_POST['emailPetugas'] ?? '');
    $no = trim($_POST['noPetugas'] ?? '');
    $jabatan = trim($_POST['jabatanPetugas'] ?? '');
    $password = trim($_POST['passwordPetugas'] ?? '');
    $confirmPassword = trim($_POST['confirmPasswordPetugas'] ?? '');

    $isChangingPassword = $password !== '' || $confirmPassword !== '';

    $baseSchema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100)),
        v::key('email', v::email()->length(3, 100)),
        v::key('no', v::digit()->length(10, 15)),
        v::key('jabatan', v::alpha()->in(["PENGURUS", "SIGAP", "VIRTUS", "MAINTENANCE"])),
        v::key('password', v::optional(v::length(5, 100))),
        v::key('confirmPassword', v::optional(v::length(5, 100)))
    );

    $postData = [
        'nama' => $nama,
        'email' => $email,
        'no' => $no,
        'jabatan' => $jabatan,
        'password' => $password,
        'confirmPassword' => $confirmPassword,
    ];

    if (!$baseSchema->validate($postData)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Data Petugas Tidak Valid!');
        exit;
    }

    if ($isChangingPassword && $password !== $confirmPassword) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Password Tidak Cocok!');
        exit;
    }

    if ($isChangingPassword) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($db, "UPDATE petugas SET NamaPetugas = ?, Email = ?, Jabatan = ?, NoHP = ?, Password = ? WHERE PetugasID = ?");
        mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $email, $jabatan, $no, $hashedPassword, $id);
    } else {
        $stmt = mysqli_prepare($db, "UPDATE petugas SET NamaPetugas = ?, Email = ?, Jabatan = ?, NoHP = ? WHERE PetugasID = ?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $email, $jabatan, $no, $id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        // TODO: Handle query execution error
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: /doremi-app/dashboard/petugas/?status=success&message=Petugas Berhasil Diupdate!");
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
                Edit Petugas
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="petugasID" class="form-label">ID Petugas</label>
                    <input type="text" class="form-control" id="petugasID"
                        value="<?= htmlspecialchars($petugas['PetugasID']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="namaPetugas" class="form-label">Nama Petugas</label>
                    <input type="text" name="namaPetugas" class="form-control" id="namaPetugas"
                        value="<?= htmlspecialchars($petugas['NamaPetugas']) ?>">
                </div>
                <div class="mb-3">
                    <label for="emailPetugas" class="form-label">Email Petugas</label>
                    <input type="email" name="emailPetugas" class="form-control" id="emailPetugas"
                        value="<?= htmlspecialchars($petugas['Email']) ?>">
                </div>
                <div class="mb-3">
                    <label for="noPetugas" class="form-label">No. HP</label>
                    <input type="number" name="noPetugas" class="form-control" id="noPetugas"
                        value="<?= htmlspecialchars($petugas['NoHP']) ?>">
                </div>
                <div class="mb-3">
                    <label for="jabatanPetugas" class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatanPetugas" id="jabatanPetugas">
                        <option disabled>Pilih Salah Satu</option>
                        <?php foreach (["PENGURUS", "SIGAP", "VIRTUS", "MAINTENANCE"] as $role): ?>
                            <option value="<?= $role ?>" <?= $petugas['Jabatan'] === $role ? 'selected' : '' ?>>
                                <?= $role ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="passwordPetugas" class="form-label">Password Baru <span
                            class="tw:text-gray-400 tw:text-sm">(kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="passwordPetugas" class="form-control" id="passwordPetugas">
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPetugas" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirmPasswordPetugas" class="form-control"
                        id="confirmPasswordPetugas">
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