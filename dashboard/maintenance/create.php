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

<body class="tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <div class="tw:flex tw:flex-col tw:gap-4 tw:md:flex-row tw:md:items-center tw:md:justify-between tw:mb-8">
                <div>
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">Buat Laporan Kerusakan</h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">Laporkan kerusakan asrama agar teknisi segera mengatasinya.</p>
                </div>
                <a href="index.php"
                    class="tw:bg-white tw:text-slate-700 tw:px-4 tw:py-3 tw:rounded-xl tw:border tw:border-slate-200 tw:hover:bg-slate-50 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                <!-- Instruction Legend / Panduan prioritas -->
                <div class="tw:lg:col-span-1">
                    <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100 tw:h-full">
                        <h5 class="tw:font-bold tw:text-slate-900 tw:mb-4">Panduan Tingkat Urgensi</h5>
                        <p class="tw:text-sm tw:text-slate-500 tw:mb-4">Gunakan panduan berikut untuk menentukan kategori kerusakan fasilitas:</p>
                        
                        <div class="tw:flex tw:flex-col tw:gap-4">
                            <div class="tw:p-3 tw:rounded-xl tw:bg-slate-50 tw:border-l-4 tw:border-l-slate-400">
                                <h6 class="tw:font-bold tw:text-slate-800 tw:text-sm tw:mb-1">Kerusakan Ringan</h6>
                                <p class="tw:text-xs tw:text-slate-600 tw:m-0">Masalah kecil yang tidak mengganggu kenyamanan vital. Contoh: engsel pintu berdecit, gantungan baju kendor.</p>
                            </div>
                            
                            <div class="tw:p-3 tw:rounded-xl tw:bg-amber-50/50 tw:border-l-4 tw:border-l-amber-500">
                                <h6 class="tw:font-bold tw:text-amber-800 tw:text-sm tw:mb-1">Kerusakan Sedang</h6>
                                <p class="tw:text-xs tw:text-amber-700 tw:m-0">Masalah yang mengganggu kenyamanan harian tetapi tidak langsung membahayakan fisik. Contoh: keran air bocor tipis, AC kurang dingin, lampu kamar berkedip.</p>
                            </div>
                            
                            <div class="tw:p-3 tw:rounded-xl tw:bg-red-50/50 tw:border-l-4 tw:border-l-red-500">
                                <h6 class="tw:font-bold tw:text-red-800 tw:text-sm tw:mb-1">Kerusakan Darurat / Berat</h6>
                                <p class="tw:text-xs tw:text-red-700 tw:m-0">Masalah kritis yang mengancam keamanan jiwa, keselamatan struktural, atau menghentikan fasilitas vital asrama. Contoh: korsleting listrik, pipa bocor parah (banjir), kunci pintu macet.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form report -->
                <div class="tw:lg:col-span-2">
                    <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100" x-data="{ targetType: 'ruangan' }">
                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4">
                                <label class="form-label tw:font-semibold">Skala Prioritas / Tingkat Kerusakan</label>
                                <select name="jenisLaporan" class="form-select" required>
                                    <option value="" selected disabled>Pilih Prioritas</option>
                                    <option value="Kerusakan Ringan">Kerusakan Ringan (Low Priority)</option>
                                    <option value="Kerusakan Sedang">Kerusakan Sedang (Medium Priority)</option>
                                    <option value="Kerusakan Darurat / Berat">Kerusakan Darurat / Berat (EMERGENCY)</option>
                                </select>
                            </div>

                            <div class="mb-4">
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

                                <!-- Ruangan -->
                                <div x-show="targetType === 'ruangan'">
                                    <select name="targetValue" class="form-select" :required="targetType === 'ruangan'">
                                        <option value="" selected disabled>Pilih Ruangan</option>
                                        <?php foreach ($ruangans as $r): ?>
                                            <option value="<?= $r['RuanganID'] ?>"><?= htmlspecialchars($r['NamaRuangan']) ?> - Lantai <?= $r['Lantai'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Inventaris -->
                                <div x-show="targetType === 'inventaris'">
                                    <select name="targetValue" class="form-select" :required="targetType === 'inventaris'">
                                        <option value="" selected disabled>Pilih Inventaris</option>
                                        <?php foreach ($inventarisList as $i): ?>
                                            <option value="<?= $i['InventarisID'] ?>"><?= htmlspecialchars($i['NamaBarang']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label tw:font-semibold">Deskripsi Masalah</label>
                                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan kronologi dan letak kerusakan..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label tw:font-semibold">Foto Bukti Kerusakan</label>
                                <input type="file" name="fotoLaporan" class="form-control" accept="image/png,image/jpeg,image/webp">
                                <div class="form-text">File foto akan otomatis dikonversi ke format string Base64 sebelum disimpan. Maksimal ukuran 2MB.</div>
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
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>
</html>