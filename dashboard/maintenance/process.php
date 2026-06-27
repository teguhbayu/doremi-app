<?php
session_start();
require 'helpers.php';

// 1. Strict Role Authorization: Only the MAINTENANCE team can execute this script
maintenance_require_roles(['MAINTENANCE']);
require '../../csrf.php';
csrf_validate('index.php');

require '../../db.php';

$action = $_POST['action'] ?? '';
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];

if (!$id) {
    maintenance_redirect('index.php', 'error', 'ID laporan tidak valid.');
}

if ($action === 'claim') {
    // Guard: only claim tickets still in 'Diajukan' status (prevents race condition)
    $stmt = mysqli_prepare($db, "UPDATE maintenance SET StatusMaintenance = 'Diproses', PetugasID = ? WHERE MaintenanceID = ? AND IsDeleted = 0 AND StatusMaintenance = 'Diajukan'");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $id);

    if (mysqli_stmt_execute($stmt)) {
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        if ($affected === 0) {
            maintenance_redirect('index.php', 'error', 'Laporan sudah diklaim oleh teknisi lain.');
        }
        maintenance_redirect('index.php', 'success', 'Laporan berhasil di-claim! Silahkan mulai perbaikan.');
    } else {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'error', 'Gagal memproses klaim pekerjaan.');
    }
}

elseif ($action === 'complete') {
    // Ownership check: only the claiming technician can mark their own ticket complete
    $ownerStmt = mysqli_prepare($db, "SELECT MaintenanceID FROM maintenance WHERE MaintenanceID = ? AND PetugasID = ? AND StatusMaintenance = 'Diproses' AND IsDeleted = 0 LIMIT 1");
    mysqli_stmt_bind_param($ownerStmt, 'ii', $id, $userId);
    mysqli_stmt_execute($ownerStmt);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($ownerStmt))) {
        mysqli_stmt_close($ownerStmt);
        maintenance_redirect('index.php', 'error', 'Anda tidak memiliki wewenang untuk menyelesaikan laporan ini.');
    }
    mysqli_stmt_close($ownerStmt);

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

    $stmt = mysqli_prepare(
        $db,
        "UPDATE maintenance SET StatusMaintenance = 'Selesai', TanggalSelesai = ?, Keterangan = ?, FotoMaintenance = ? WHERE MaintenanceID = ? AND IsDeleted = 0"
    );
    mysqli_stmt_bind_param($stmt, 'sssi', $tanggalSelesai, $keterangan, $fotoMaintenance, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'success', 'Perbaikan selesai! Laporan berhasil diperbarui.');
    } else {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'error', 'Terjadi kesalahan sistem saat memperbarui status selesai.');
    }
} else {
    maintenance_redirect('index.php');
}