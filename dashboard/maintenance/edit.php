<?php
session_start();
require 'helpers.php';
// Allowed the MAINTENANCE role to edit their submitted tickets
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../csrf.php';
require '../../db.php';

use Respect\Validation\Validator as v;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];
$role = $_SESSION['userRole'];

if (!$id) {
    maintenance_redirect('index.php', 'error', 'ID laporan tidak valid.');
}

$stmt = mysqli_prepare($db, "SELECT * FROM maintenance WHERE MaintenanceID = ? AND IsDeleted = 0 LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$report = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$report) {
    maintenance_redirect('index.php', 'error', 'Laporan tidak ditemukan.');
}

if ($report['StatusMaintenance'] !== 'Diajukan') {
    maintenance_redirect('index.php', 'error', 'Laporan yang sedang diproses atau selesai tidak dapat diubah.');
}

$isOwner = false;
if ($role === 'PENGHUNI') {
    if ((int)$report['PenghuniID'] === $userId) {
        $isOwner = true;
    }
} else {
    // Correctly identifies ownership for staff creators (including the MAINTENANCE technician)
    if ((int)$report['PetugasID'] === $userId && $report['PenghuniID'] === null) {
        $isOwner = true;
    }
}

if (!$isOwner) {
    maintenance_redirect('index.php', 'error', 'Anda tidak memiliki hak akses untuk mengedit laporan ini.');
}

$ruanganQuery = mysqli_query($db, "SELECT RuanganID, NamaRuangan, Lantai FROM ruangan WHERE IsDeleted = 0 ORDER BY NamaRuangan ASC");
$ruangans = mysqli_fetch_all($ruanganQuery, MYSQLI_ASSOC);

$inventarisQuery = mysqli_query($db, "SELECT InventarisID, NamaBarang FROM inventaris WHERE IsDeleted = 0 ORDER BY NamaBarang ASC");
$inventarisList = mysqli_fetch_all($inventarisQuery, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_SERVER['PHP_SELF'] . '?id=' . $id);
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
        maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', 'Data input tidak valid.');
    }

    $ruanganId = null;
    $inventarisId = null;

    if ($targetType === 'ruangan') {
        $ruanganId = (int)$targetValue;
        // Validate that the ruangan actually exists
        $chk = mysqli_prepare($db, "SELECT RuanganID FROM ruangan WHERE RuanganID = ? AND IsDeleted = 0 LIMIT 1");
        mysqli_stmt_bind_param($chk, 'i', $ruanganId);
        mysqli_stmt_execute($chk);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            mysqli_stmt_close($chk);
            maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', 'Ruangan yang dipilih tidak ditemukan.');
        }
        mysqli_stmt_close($chk);
    } else {
        $inventarisId = (int)$targetValue;
        // Validate that the inventaris actually exists
        $chk = mysqli_prepare($db, "SELECT InventarisID FROM inventaris WHERE InventarisID = ? AND IsDeleted = 0 LIMIT 1");
        mysqli_stmt_bind_param($chk, 'i', $inventarisId);
        mysqli_stmt_execute($chk);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            mysqli_stmt_close($chk);
            maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', 'Inventaris yang dipilih tidak ditemukan.');
        }
        mysqli_stmt_close($chk);
    }

    try {
        $fotoLaporan = maintenance_store_photo($_FILES['fotoLaporan'] ?? [], $report['FotoLaporan']);
    } catch (RuntimeException $e) {
        maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', $e->getMessage());
    }

    $updateStmt = mysqli_prepare(
        $db,
        "UPDATE maintenance SET RuanganID = ?, InventarisID = ?, JenisLaporan = ?, Deskripsi = ?, FotoLaporan = ? WHERE MaintenanceID = ?"
    );
    mysqli_stmt_bind_param(
        $updateStmt,
        'iisssi',
        $ruanganId,
        $inventarisId,
        $jenisLaporan,
        $deskripsi,
        $fotoLaporan,
        $id
    );

    if (mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);
        maintenance_redirect('index.php', 'success', 'Laporan kerusakan berhasil diperbarui!');
    } else {
        mysqli_stmt_close($updateStmt);
        maintenance_redirect($_SERVER['PHP_SELF'] . '?id=' . $id, 'error', 'Terjadi kesalahan sistem saat menyimpan perubahan.');
    }
}

$currentTargetType = !empty($report['RuanganID']) ? 'ruangan' : 'inventaris';
$currentTargetValue = !empty($report['RuanganID']) ? $report['RuanganID'] : $report['InventarisID'];
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perbarui Laporan" data-subtitle="Ubah prioritas, target, deskripsi, dan bukti foto sebelum laporan masuk ke tahap proses penanganan teknisi.">
                Edit Laporan
            </h1>

            <div class="page-toolbar" data-note="Hanya laporan berstatus diajukan yang dapat diperbarui">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                <div class="tw:lg:col-span-1">
                    <div class="dashboard-side-panel tw:h-full">
                        <h5 class="dashboard-side-panel__title">Panduan Tingkat Urgensi</h5>
                        <p class="dashboard-side-panel__copy">Sesuaikan kategori tingkat kerusakan agar prioritas pengerjaan tetap akurat saat laporan diperbarui.</p>

                        <div class="dashboard-guide-list">
                            <div class="dashboard-guide-item">
                                <strong>Kerusakan Ringan</strong>
                                <p>Masalah kecil yang tidak mengganggu fungsi vital asrama.</p>
                            </div>

                            <div class="dashboard-guide-item dashboard-guide-item--warning">
                                <strong>Kerusakan Sedang</strong>
                                <p>Masalah yang mengurangi kenyamanan asrama tetapi tidak darurat.</p>
                            </div>

                            <div class="dashboard-guide-item dashboard-guide-item--danger">
                                <strong>Kerusakan Darurat / Berat</strong>
                                <p>Masalah darurat yang membahayakan struktural atau keselamatan penghuni.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw:lg:col-span-2">
                    <form method="POST" enctype="multipart/form-data" class="form-shell" x-data="{ targetType: '<?= $currentTargetType ?>' }">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <label class="form-label tw:font-semibold">Skala Prioritas / Tingkat Kerusakan</label>
                                <select name="jenisLaporan" class="form-select" required>
                                    <option value="Kerusakan Ringan" <?= $report['JenisLaporan'] === 'Kerusakan Ringan' ? 'selected' : '' ?>>Kerusakan Ringan (Low Priority)</option>
                                    <option value="Kerusakan Sedang" <?= $report['JenisLaporan'] === 'Kerusakan Sedang' ? 'selected' : '' ?>>Kerusakan Sedang (Medium Priority)</option>
                                    <option value="Kerusakan Darurat / Berat" <?= $report['JenisLaporan'] === 'Kerusakan Darurat / Berat' ? 'selected' : '' ?>>Kerusakan Darurat / Berat (EMERGENCY)</option>
                                </select>
                            </div>

                            <div class="mb-4 form-shell__full">
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

                            <div class="mb-4 form-shell__full">
                                <label class="form-label tw:font-semibold">Deskripsi Kerusakan</label>
                                <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($report['Deskripsi']) ?></textarea>
                            </div>

                            <div class="mb-4 form-shell__full">
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

                            <div class="tw:w-full tw:flex tw:justify-end tw:mt-4">
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
