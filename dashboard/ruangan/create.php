<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$ruanganTypes = ['Public Space', 'Ruang Ibadah', 'Ruang Belajar', 'Ruang Organisasi', 'Area Olahraga', 'Area Parkir', 'Area Servis'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaRuangan'] ?? '');
    $jenis = trim($_POST['jenisRuangan'] ?? '');
    $lantai = trim($_POST['lantaiRuangan'] ?? '');
    $keterangan = trim($_POST['keteranganRuangan'] ?? '');

    $ruanganSchema = v::keySet(
        v::key('nama', v::stringType()->length(1, 100)),
        v::key('jenis', v::in($ruanganTypes)),
        v::key('lantai', v::in(['1', '2', '3', '4', '5', '6', '7'])),
        v::key('keterangan', v::stringType()->length(0, 500))
    );

    $postData = [
        'nama' => $nama,
        'jenis' => $jenis,
        'lantai' => $lantai,
        'keterangan' => $keterangan,
    ];

    if (!$ruanganSchema->validate($postData)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data Ruangan tidak Valid!');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "INSERT INTO ruangan (NamaRuangan, JenisRuangan, Lantai, Keterangan, UpdatedAt, IsDeleted) VALUES (?, ?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'sssss', $nama, $jenis, $lantai, $keterangan, $now);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menyimpan data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: " . '/doremi-app/dashboard/ruangan/' . '?status=success&message=Ruangan Berhasil Ditambahkan!');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-[20.5rem] tw:grow">
        <div class="dashboard-page tw:pt-24 tw:md:pt-9 tw:px-4 tw:md:px-8 tw:pb-8 tw:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Buat entri ruangan baru dengan informasi lantai, jenis, dan catatan operasional yang lengkap.">
                Tambah Ruangan
            </h1>
            <div class="page-toolbar" data-note="Form ruangan baru">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="form-shell">
                <div class="mb-3">
                    <label for="namaRuangan" class="form-label">Nama Ruangan</label>
                    <input type="text" name="namaRuangan" class="form-control" id="namaRuangan" required>
                </div>
                <div class="mb-3">
                    <label for="jenisRuangan" class="form-label">Jenis Ruangan</label>
                    <select class="form-select" name="jenisRuangan" id="jenisRuangan" required>
                        <option value="" disabled selected>Pilih Jenis Ruangan</option>
                        <?php foreach ($ruanganTypes as $jenisRuangan): ?>
                            <option value="<?= htmlspecialchars($jenisRuangan) ?>"><?= htmlspecialchars($jenisRuangan) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Jenis ruangan dibuat statis supaya pengelompokan data tetap konsisten.</span>
                </div>
                <div class="mb-3">
                    <label for="lantaiRuangan" class="form-label">Lantai</label>
                    <select class="form-select" name="lantaiRuangan" id="lantaiRuangan" required>
                        <option value="" disabled selected>Pilih Lantai</option>
                        <option value="1">Lantai 1</option>
                        <option value="2">Lantai 2</option>
                        <option value="3">Lantai 3</option>
                        <option value="4">Lantai 4</option>
                        <option value="5">Lantai 5</option>
                        <option value="6">Lantai 6</option>
                        <option value="7">Lantai 7</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="keteranganRuangan" class="form-label">Keterangan</label>
                    <textarea name="keteranganRuangan" class="form-control" id="keteranganRuangan" rows="3"></textarea>
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
