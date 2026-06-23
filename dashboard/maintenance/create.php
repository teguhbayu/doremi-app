<?php
session_start();
require 'helpers.php';
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../db.php';

use Respect\Validation\Validator as v;

$ruanganQuery = mysqli_query($db, "SELECT RuanganID, NamaRuangan, Lantai FROM ruangan WHERE IsDeleted = 0 ORDER BY NamaRuangan ASC");
$ruangans = mysqli_fetch_all($ruanganQuery, MYSQLI_ASSOC);

$inventarisQuery = mysqli_query($db, "SELECT InventarisID, NamaBarang FROM inventaris WHERE IsDeleted = 0 ORDER BY NamaBarang ASC");
$inventarisList = mysqli_fetch_all($inventarisQuery, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenisLaporan = trim($_POST['jenisLaporan'] ?? '');
    $targetType = trim($_POST['targetType'] ?? '');
    $targetValue = trim($_POST['targetValue'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $schema = v::keySet(
        v::key('jenisLaporan', v::in(['Kerusakan Ringan', 'Kerusakan Sedang', 'Kerusakan Darurat / Berat'])),
        v::key('targetType', v::in(['ruangan', 'inventaris'])),
        v::key('targetValue', v::numericVal()),
        v::key('deskripsi', v::stringType()->length(1, 1000))
    );

    $postData = [
        'jenisLaporan' => $jenisLaporan,
        'targetType' => $targetType,
        'targetValue' => $targetValue,
        'deskripsi' => $deskripsi
    ];

    if (!$schema->validate($postData)) {
        maintenance_redirect($_SERVER['PHP_SELF'], 'error', 'Data input tidak valid.');
    }

    $ruanganId = null;
    $inventarisId = null;

    if ($targetType === 'ruangan') {
        $ruanganId = (int)$targetValue;
    } else {
        $inventarisId = (int)$targetValue;
    }

    try {
        $fotoLaporan = maintenance_store_photo($_FILES['fotoLaporan'] ?? []);
    } catch (RuntimeException $e) {
        maintenance_redirect($_SERVER['PHP_SELF'], 'error', $e->getMessage());
    }

    $userId = (int)$_SESSION['userId'];
    $role = $_SESSION['userRole'];

    $penghuniId = null;
    $petugasId = null;

    if ($role === 'PENGHUNI') {
        $penghuniId = $userId;
    } else {
        $petugasId = $userId;
    }

    $tanggalLapor = date('Y-m-d');
    $statusMaintenance = 'Diajukan';

    $stmt = mysqli_prepare(
        $db,
        "INSERT INTO maintenance (
            PenghuniID, 
            PetugasID, 
            RuanganID, 
            InventarisID, 
            TanggalLapor, 
            JenisLaporan, 
            Deskripsi, 
            StatusMaintenance, 
            FotoLaporan,
            FotoMaintenance,
            TanggalSelesai,
            Keterangan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL)"
    );
    mysqli_stmt_bind_param(
        $stmt,
        'iiiisssss',
        $penghuniId,
        $petugasId,
        $ruanganId,
        $inventarisId,
        $tanggalLapor,
        $jenisLaporan,
        $deskripsi,
        $statusMaintenance,
        $fotoLaporan
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        maintenance_redirect('/doremi-app/dashboard/maintenance/', 'success', 'Laporan kerusakan berhasil dikirim!');
    } else {
        mysqli_stmt_close($stmt);
        maintenance_redirect($_SERVER['PHP_SELF'], 'error', 'Terjadi kesalahan sistem saat menyimpan data.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Laporan" data-subtitle="Laporkan kerusakan asrama dengan target yang jelas, tingkat urgensi yang tepat, dan bukti foto agar penanganan lebih cepat.">
                Buat Laporan Kerusakan
            </h1>

            <div class="page-toolbar" data-note="Form laporan kerusakan baru">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                <div class="tw:lg:col-span-1">
                    <div class="dashboard-side-panel tw:h-full">
                        <h5 class="dashboard-side-panel__title">Panduan Tingkat Urgensi</h5>
                        <p class="dashboard-side-panel__copy">Gunakan panduan berikut untuk menentukan kategori kerusakan fasilitas sebelum laporan dikirim.</p>

                        <div class="dashboard-guide-list">
                            <div class="dashboard-guide-item">
                                <strong>Kerusakan Ringan</strong>
                                <p>Masalah kecil yang tidak mengganggu kenyamanan vital. Contoh: engsel pintu berdecit, gantungan baju kendor.</p>
                            </div>

                            <div class="dashboard-guide-item dashboard-guide-item--warning">
                                <strong>Kerusakan Sedang</strong>
                                <p>Masalah yang mengganggu kenyamanan harian tetapi tidak langsung membahayakan fisik. Contoh: keran air bocor tipis, AC kurang dingin, lampu kamar berkedip.</p>
                            </div>

                            <div class="dashboard-guide-item dashboard-guide-item--danger">
                                <strong>Kerusakan Darurat / Berat</strong>
                                <p>Masalah kritis yang mengancam keamanan jiwa, keselamatan struktural, atau menghentikan fasilitas vital asrama. Contoh: korsleting listrik, pipa bocor parah, kunci pintu macet.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw:lg:col-span-2">
                    <form method="POST" enctype="multipart/form-data" class="form-shell" x-data="{ targetType: 'ruangan' }">
                            <div class="mb-4">
                                <label class="form-label tw:font-semibold">Skala Prioritas / Tingkat Kerusakan</label>
                                <select name="jenisLaporan" class="form-select" required>
                                    <option value="" selected disabled>Pilih Prioritas</option>
                                    <option value="Kerusakan Ringan">Kerusakan Ringan (Low Priority)</option>
                                    <option value="Kerusakan Sedang">Kerusakan Sedang (Medium Priority)</option>
                                    <option value="Kerusakan Darurat / Berat">Kerusakan Darurat / Berat (EMERGENCY)</option>
                                </select>
                            </div>

                            <div class="mb-4 form-shell__full">
                                <label class="form-label tw:font-semibold">Target Lokasi Laporan</label>
                                <div class="tw:flex tw:gap-4 tw:mb-2">
                                    <label class="tw:inline-flex tw:items-center">
                                        <input type="radio" name="targetType" value="ruangan" x-model="targetType" class="form-check-input tw:mr-2">
                                        <span>Ruangan</span>
                                    </label>
                                    <label class="tw:inline-flex tw:items-center">
                                        <input type="radio" name="targetType" value="inventaris" x-model="targetType" class="form-check-input tw:mr-2">
                                        <span>Inventaris / Barang</span>
                                    </label>
                                </div>

                                <div x-show="targetType === 'ruangan'">
                                    <select name="targetValue" class="form-select" :required="targetType === 'ruangan'">
                                        <option value="" selected disabled>Pilih Ruangan</option>
                                        <?php foreach ($ruangans as $r): ?>
                                            <option value="<?= $r['RuanganID'] ?>"><?= htmlspecialchars($r['NamaRuangan']) ?> - Lantai <?= $r['Lantai'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div x-show="targetType === 'inventaris'">
                                    <select name="targetValue" class="form-select" :required="targetType === 'inventaris'">
                                        <option value="" selected disabled>Pilih Inventaris</option>
                                        <?php foreach ($inventarisList as $i): ?>
                                            <option value="<?= $i['InventarisID'] ?>"><?= htmlspecialchars($i['NamaBarang']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 form-shell__full">
                                <label class="form-label tw:font-semibold">Deskripsi Masalah</label>
                                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan kronologi dan letak kerusakan..." required></textarea>
                            </div>

                            <div class="mb-4 form-shell__full">
                                <label class="form-label tw:font-semibold">Foto Bukti Kerusakan</label>
                                <input type="file" name="fotoLaporan" class="form-control" accept="image/png,image/jpeg,image/webp">
                                <div class="form-text">Maksimal ukuran 2MB.</div>
                            </div>

                            <div class="tw:w-full tw:flex tw:justify-end tw:mt-4">
                                <button type="submit"
                                    class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                                    <span>Kirim Laporan Kerusakan</span>
                                </button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>
</html>
