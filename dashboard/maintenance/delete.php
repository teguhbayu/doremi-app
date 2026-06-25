<?php
session_start();
require 'helpers.php';
// Added MAINTENANCE to the exception (they cannot delete reports, only the creator can)
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];
$role = $_SESSION['userRole'];

if (!$id) {
    maintenance_redirect('index.php', 'error', 'ID laporan tidak valid.');
}

// Fetch report details
$stmt = mysqli_prepare($db, "SELECT * FROM maintenance WHERE MaintenanceID = ? AND IsDeleted = 0 LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$report = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$report) {
    maintenance_redirect('index.php', 'error', 'Laporan tidak ditemukan.');
}

// Validation: Only allow deletion if status is still "Diajukan"
if ($report['StatusMaintenance'] !== 'Diajukan') {
    maintenance_redirect('index.php', 'error', 'Laporan yang sedang diproses atau sudah selesai tidak dapat dihapus.');
}

// Ownership verification
$isOwner = false;
if ($role === 'PENGHUNI') {
    if ((int)$report['PenghuniID'] === $userId) {
        $isOwner = true;
    }
} else {
    // Allows maintenance technicians to delete their own created tickets as well
    if ((int)$report['PetugasID'] === $userId && $report['PenghuniID'] === null) {
        $isOwner = true;
    }
}

if (!$isOwner) {
    maintenance_redirect('index.php', 'error', 'Anda tidak memiliki wewenang untuk menghapus laporan ini.');
}

// Execute soft delete
$deleteStmt = mysqli_prepare($db, "UPDATE maintenance SET IsDeleted = 1 WHERE MaintenanceID = ?");
mysqli_stmt_bind_param($deleteStmt, 'i', $id);

if (mysqli_stmt_execute($deleteStmt)) {
    mysqli_stmt_close($deleteStmt);
    maintenance_redirect('index.php', 'success', 'Laporan kerusakan berhasil dihapus.');
} else {
    mysqli_stmt_close($deleteStmt);
    maintenance_redirect('index.php', 'error', 'Gagal menghapus laporan.');
}