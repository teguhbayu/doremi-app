<?php
session_start();
require 'helpers.php';

// 1. Strict Role Authorization: Only the MAINTENANCE team can execute this script
maintenance_require_roles(['MAINTENANCE']);
require '../../csrf.php';
csrf_validate('index.php');

require '../../db.php';
require_once '../../database/maintenance.php';

$action = $_POST['action'] ?? '';
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];

if (!$id) {
    maintenance_redirect('index.php', 'error', 'ID laporan tidak valid.');
}

if ($action === 'claim') {
    try {
        $affected = claimMaintenanceReport($db, $id, $userId);
        if ($affected === 0) {
            maintenance_redirect('index.php', 'error', 'Laporan sudah diklaim oleh teknisi lain.');
        }
        maintenance_redirect('index.php', 'success', 'Laporan berhasil di-claim! Silahkan mulai perbaikan.');
    } catch (RuntimeException) {
        maintenance_redirect('index.php', 'error', 'Gagal memproses klaim pekerjaan.');
    }
}

elseif ($action === 'complete') {
    if (!checkMaintenanceTechnicianOwnership($db, $id, $userId)) {
        maintenance_redirect('index.php', 'error', 'Anda tidak memiliki wewenang untuk menyelesaikan laporan ini.');
    }

    $keterangan = trim($_POST['keterangan'] ?? '');
    $tanggalSelesai = date('Y-m-d');

    if (empty($keterangan)) {
        maintenance_redirect('index.php', 'error', 'Keterangan hasil perbaikan harus diisi.');
    }

    try {
        $fotoMaintenance = maintenance_store_photo($_FILES['fotoMaintenance'] ?? []);
    } catch (RuntimeException $e) {
        maintenance_redirect('index.php', 'error', $e->getMessage());
    }

    if (empty($fotoMaintenance)) {
        maintenance_redirect('index.php', 'error', 'Foto hasil perbaikan wajib diunggah.');
    }

    try {
        completeMaintenanceReport($db, $id, $tanggalSelesai, $keterangan, $fotoMaintenance);
        maintenance_redirect('index.php', 'success', 'Perbaikan selesai! Laporan berhasil diperbarui.');
    } catch (RuntimeException) {
        maintenance_redirect('index.php', 'error', 'Terjadi kesalahan sistem saat memperbarui status selesai.');
    }
} else {
    maintenance_redirect('index.php');
}
