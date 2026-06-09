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
    $nomor = trim($_POST['nomorKamar'] ?? '');
    $kapasitas = trim($_POST['kapasitasKamar'] ?? '');

    $kamarSchema = v::keySet(
        v::key('nomor', v::stringType()->length(1, 20)),
        v::key('kapasitas', v::numericVal()->positive())
    );

    $postData = [
        'nomor' => $nomor,
        'kapasitas' => $kapasitas,
    ];

    if (!$kamarSchema->validate($postData)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data Kamar tidak Valid!');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "INSERT INTO kamar (NomorKamar, KapasitasPenghuni, UpdatedAt, IsDeleted) VALUES (?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'sis', $nomor, $kapasitas, $now);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menyimpan data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: " . '/doremi-app/dashboard/kamar/' . '?status=success&message=Kamar Berhasil Ditambahkan!');
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
                Tambah Kamar
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="nomorKamar" class="form-label">Nomor Kamar</label>
                    <input type="text" name="nomorKamar" class="form-control" id="nomorKamar" required>
                </div>
                <div class="mb-3">
                    <label for="kapasitasKamar" class="form-label">Kapasitas Penghuni</label>
                    <input type="number" name="kapasitasKamar" class="form-control" id="kapasitasKamar" required>
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
