<?php
session_start();
require 'helpers.php';
maintenance_require_roles(['PENGURUS', 'MAINTENANCE']);
require '../../db.php';

$action = $_POST['action'] ?? '';
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];

if (!$id) {
    maintenance_redirect('index.php', 'error', 'ID laporan tidak valid.');
}

if ($action === 'claim') {
    // Claiming status transition: 'Diajukan' -> 'Diproses' and logs the claimed technicians' ID
    $stmt = mysqli_prepare($db, "UPDATE maintenance SET StatusMaintenance = 'Diproses', PetugasID = ? WHERE MaintenanceID = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'success', 'Laporan berhasil di-claim! Pekerjaan telah masuk status Diproses.');
    } else {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'error', 'Gagal memproses klaim pekerjaan.');
    }
}

elseif ($action === 'complete') {
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
        maintenance_redirect('index.php', 'error', 'Foto bukti hasil perbaikan wajib diunggah.');
    }

    // Resolving status transition: 'Diproses' -> 'Selesai' with final reporting metrics
    $stmt = mysqli_prepare(
        $db,
        "UPDATE maintenance SET StatusMaintenance = 'Selesai', TanggalSelesai = ?, Keterangan = ?, FotoMaintenance = ? WHERE MaintenanceID = ?"
    );
    mysqli_stmt_bind_param($stmt, 'sssi', $tanggalSelesai, $keterangan, $fotoMaintenance, $id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'success', 'Perbaikan selesai! Pelapor sekarang dapat melihat laporan hasil kerja.');
    } else {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'error', 'Terjadi kesalahan sistem saat memperbarui status selesai.');
    }
} else {
    maintenance_redirect('index.php');
}