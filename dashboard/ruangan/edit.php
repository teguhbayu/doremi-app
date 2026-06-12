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
    header("Location: /doremi-app/dashboard/ruangan/");
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM ruangan WHERE RuanganID = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ruangan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$ruangan) {
    header("Location: /doremi-app/dashboard/ruangan/");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaRuangan'] ?? '');
    $jenis = trim($_POST['jenisRuangan'] ?? '');
    $keterangan = trim($_POST['keteranganRuangan'] ?? '');

    $ruanganSchema = v::keySet(
        v::key('nama', v::stringType()->length(1, 100)),
        v::key('jenis', v::stringType()->length(1, 50)),
        v::key('keterangan', v::stringType()->length(0, 500))
    );

    $postData = [
        'nama' => $nama,
        'jenis' => $jenis,
        'keterangan' => $keterangan,
    ];

    if (!$ruanganSchema->validate($postData)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Data Ruangan Tidak Valid!');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "UPDATE ruangan SET NamaRuangan = ?, JenisRuangan = ?, Keterangan = ?, UpdatedAt = ? WHERE RuanganID = ?");
    mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $jenis, $keterangan, $now, $id);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Terjadi Kesalahan saat mengupdate data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: /doremi-app/dashboard/ruangan/?status=success&message=Ruangan Berhasil Diupdate!");
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
                Edit Ruangan
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="ruanganID" class="form-label">ID Ruangan</label>
                    <input type="text" class="form-control" id="ruanganID"
                        value="<?= htmlspecialchars($ruangan['RuanganID']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="namaRuangan" class="form-label">Nama Ruangan</label>
                    <input type="text" name="namaRuangan" class="form-control" id="namaRuangan"
                        value="<?= htmlspecialchars($ruangan['NamaRuangan']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="jenisRuangan" class="form-label">Jenis Ruangan</label>
                    <input type="text" name="jenisRuangan" class="form-control" id="jenisRuangan"
                        value="<?= htmlspecialchars($ruangan['JenisRuangan']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="keteranganRuangan" class="form-label">Keterangan</label>
                    <textarea name="keteranganRuangan" class="form-control" id="keteranganRuangan"
                        rows="3"><?= htmlspecialchars($ruangan['Keterangan']) ?></textarea>
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
