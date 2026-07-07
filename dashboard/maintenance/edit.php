<?php
session_start();
require 'helpers.php';
// Allowed the MAINTENANCE role to edit their submitted tickets
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../csrf.php';
require '../../db.php';
require_once '../../database/maintenance.php';
require 'validation.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];
$role = $_SESSION['userRole'];

if (!$id) {
    maintenance_redirect('index.php', 'error', 'ID laporan tidak valid.');
}

$report = fetchMaintenanceReportById($db, $id);

if (!$report) {
    maintenance_redirect('index.php', 'error', 'Laporan tidak ditemukan.');
}

if ($report['StatusMaintenance'] !== 'Diajukan') {
    maintenance_redirect('index.php', 'error', 'Laporan yang sedang diproses atau selesai tidak dapat diubah.');
}

$isOwner = isMaintenanceReportOwner($report, $role, $userId);

if (!$isOwner) {
    maintenance_redirect('index.php', 'error', 'Anda tidak memiliki hak akses untuk mengedit laporan ini.');
}

$ruangans = fetchMaintenanceRooms($db);
$inventarisList = fetchMaintenanceInventory($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_SERVER['PHP_SELF'] . '?id=' . $id);
    $reportInput = collectMaintenanceReportInput($_POST);
    $validationMessage = validateMaintenanceReportInput($db, $reportInput);
    if ($validationMessage !== null) {
        maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', $validationMessage);
    }
    $targetIds = resolveMaintenanceTargetIds($reportInput);

    try {
        $fotoLaporan = maintenance_store_photo($_FILES['fotoLaporan'] ?? [], $report['FotoLaporan']);
    } catch (RuntimeException $e) {
        maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', $e->getMessage());
    }

    try {
        updateMaintenanceReport(
            $db,
            $id,
            $targetIds['ruanganId'],
            $targetIds['inventarisId'],
            $reportInput['jenisLaporan'],
            $reportInput['deskripsi'],
            $fotoLaporan
        );
        maintenance_redirect('index.php', 'success', 'Laporan kerusakan berhasil diperbarui!');
    } catch (RuntimeException) {
        maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', 'Terjadi kesalahan sistem saat menyimpan perubahan.');
    }
}

$currentTargetType = !empty($report['RuanganID']) ? 'ruangan' : 'inventaris';
$currentTargetValue = !empty($report['RuanganID']) ? $report['RuanganID'] : $report['InventarisID'];
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perbarui Laporan" data-subtitle="Ubah prioritas, target, deskripsi, dan bukti foto sebelum laporan masuk ke tahap proses penanganan teknisi.">
                Edit Laporan
            </h1>

            <div class="page-toolbar" data-note="Hanya laporan berstatus diajukan yang dapat diperbarui">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                <div class="tw:lg:col-span-1">
                    <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm tw:h-full">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Panduan Tingkat Urgensi</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Sesuaikan kategori tingkat kerusakan agar prioritas pengerjaan tetap akurat saat laporan diperbarui.</p>

                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.10)] tw:border-l-4 tw:border-l-[rgba(20,108,148,0.30)]">
                                <strong>Kerusakan Ringan</strong>
                                <p>Masalah kecil yang tidak mengganggu fungsi vital asrama.</p>
                            </div>

                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(250,236,207,0.55)] tw:border tw:border-l-4 tw:border-[rgba(212,141,47,0.20)] tw:border-l-amber-500">
                                <strong>Kerusakan Sedang</strong>
                                <p>Masalah yang mengurangi kenyamanan asrama tetapi tidak darurat.</p>
                            </div>

                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(245,221,218,0.55)] tw:border tw:border-l-4 tw:border-[rgba(188,79,69,0.20)] tw:border-l-red-500">
                                <strong>Kerusakan Darurat / Berat</strong>
                                <p>Masalah darurat yang membahayakan struktural atau keselamatan penghuni.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw:lg:col-span-2">
                    <form method="POST" enctype="multipart/form-data" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm" x-data="{ targetType: '<?= $currentTargetType ?>' }">
                            <?php echo csrf_field(); ?>
                            
                      <div class="mb-4">
                                <label class="form-label tw:font-semibold">Skala Prioritas / Tingkat Kerusakan</label>
                                <select name="jenisLaporan" class="form-select" required>
                                    <option value="Kerusakan Ringan" <?= $report['JenisLaporan'] === 'Kerusakan Ringan' ? 'selected' : '' ?>>Kerusakan Ringan (Low Priority)</option>
                                    <option value="Kerusakan Sedang" <?= $report['JenisLaporan'] === 'Kerusakan Sedang' ? 'selected' : '' ?>>Kerusakan Sedang (Medium Priority)</option>
                                    <option value="Kerusakan Darurat / Berat" <?= $report['JenisLaporan'] === 'Kerusakan Darurat / Berat' ? 'selected' : '' ?>>Kerusakan Darurat / Berat (EMERGENCY)</option>
                                </select>
                            </div>

                            <div class="mb-4 tw:col-span-full">
                                <label class="form-label tw:font-semibold">Target / Objek Lokasi</label>
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
                                        <option value="" disabled>Pilih Ruangan</option>
                                        <?php foreach ($ruangans as $r): ?>
                                            <option value="<?= $r['RuanganID'] ?>" <?= ($currentTargetType === 'ruangan' && $currentTargetValue == $r['RuanganID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($r['NamaRuangan']) ?> - Lantai <?= $r['Lantai'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div x-show="targetType === 'inventaris'">
                                    <select name="targetValue" class="form-select" :required="targetType === 'inventaris'">
                                        <option value="" disabled>Pilih Inventaris</option>
                                        <?php foreach ($inventarisList as $i): ?>
                                            <option value="<?= $i['InventarisID'] ?>" <?= ($currentTargetType === 'inventaris' && $currentTargetValue == $i['InventarisID']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($i['NamaBarang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 tw:col-span-full">
                                <label class="form-label tw:font-semibold">Deskripsi Kerusakan</label>
                                <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($report['Deskripsi']) ?></textarea>
                            </div>

                            <div class="mb-4 tw:col-span-full">
                                <label class="form-label tw:font-semibold">Foto Bukti Kerusakan</label>
                                <?php if (!empty($report['FotoLaporan'])): ?>
                                    <div class="tw:mb-3">
                                        <p class="tw:text-xs tw:text-slate-500 tw:mb-1">Foto Terunggah:</p>
                                        <img src="<?= $report['FotoLaporan'] ?>" alt="Foto Laporan" class="tw:max-h-40 tw:rounded-lg tw:border">
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="fotoLaporan" class="form-control" accept="image/png,image/jpeg,image/webp">
                                <div class="form-text">Biarkan kosong jika tidak ingin merubah foto. Maksimal ukuran file 2MB (JPG/PNG).</div>
                            </div>

                            <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-4">
                                <button type="submit"
                                    class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                                    <span>Simpan Perubahan</span>
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
