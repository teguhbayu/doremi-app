<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$kamars = mysqli_fetch_all(mysqli_query($db, "SELECT KamarID, NomorKamar FROM kamar WHERE IsDeleted = 0"), MYSQLI_ASSOC);
$ruangans = mysqli_fetch_all(mysqli_query($db, "SELECT RuanganID, NamaRuangan FROM ruangan WHERE IsDeleted = 0"), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaBarang'] ?? '');
    $jumlah = trim($_POST['jumlahBarang'] ?? '');
    $lokasi = $_POST['lokasiBarang'] ?? '';
    $keterangan = trim($_POST['keteranganBarang'] ?? '');

    $inventarisSchema = v::keySet(
        v::key('nama', v::stringType()->length(1, 100)),
        v::key('jumlah', v::numericVal()->min(0)),
        v::key('lokasi', v::stringType()->length(1, 50)),
        v::key('keterangan', v::stringType()->length(0, 500))
    );

    if (!$inventarisSchema->validate(['nama' => $nama, 'jumlah' => $jumlah, 'lokasi' => $lokasi, 'keterangan' => $keterangan])) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data Inventaris tidak Valid!');
        exit;
    }

    $kamarId = null;
    $ruanganId = null;

    if (str_starts_with($lokasi, 'kamar:')) {
        $kamarId = explode(':', $lokasi)[1];
    } elseif (str_starts_with($lokasi, 'ruangan:')) {
        $ruanganId = explode(':', $lokasi)[1];
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "INSERT INTO inventaris (RuanganID, KamarID, NamaBarang, Jumlah, Keterangan, UpdatedAt, IsDeleted) VALUES (?, ?, ?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'iisiss', $ruanganId, $kamarId, $nama, $jumlah, $keterangan, $now);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menyimpan data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: " . '/doremi-app/dashboard/inventaris/' . '?status=success&message=Inventaris Berhasil Ditambahkan!');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Catat barang baru lengkap dengan jumlah, lokasi, dan catatan agar inventaris tetap tertib.">
                Tambah Inventaris
            </h1>
            <div class="page-toolbar" data-note="Form inventaris baru">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="form-shell">
                <div class="mb-3">
                    <label for="namaBarang" class="form-label">Nama Barang</label>
                    <input type="text" name="namaBarang" class="form-control" id="namaBarang" maxlength="100" required>
                </div>
                <div class="mb-3">
                    <label for="jumlahBarang" class="form-label">Jumlah</label>
                    <input type="number" name="jumlahBarang" class="form-control" id="jumlahBarang" required>
                </div>
                <div class="mb-3">
                    <label for="lokasiBarang" class="form-label">Lokasi</label>
                    <select class="form-select" name="lokasiBarang" id="lokasiBarang" required>
                        <option selected disabled>Pilih Lokasi</option>
                        <optgroup label="Kamar">
                            <?php foreach ($kamars as $k): ?>
                                <option value="kamar:<?= $k['KamarID'] ?>">Kamar <?= $k['NomorKamar'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Ruangan">
                            <?php foreach ($ruangans as $r): ?>
                                <option value="ruangan:<?= $r['RuanganID'] ?>"><?= $r['NamaRuangan'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="keteranganBarang" class="form-label">Keterangan</label>
                    <textarea name="keteranganBarang" class="form-control" id="keteranganBarang" rows="3"></textarea>
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
