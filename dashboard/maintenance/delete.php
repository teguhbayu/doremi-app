<?php
session_start();
require 'helpers.php';
// Added MAINTENANCE to the exception (they cannot delete reports, only the creator can)
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../csrf.php';

// Only accept POST to prevent CSRF via URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    maintenance_redirect('dashboard/maintenance/');
}
    csrf_validate('dashboard/maintenance/');

require '../../db.php';
require_once '../../database/maintenance.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$userId = (int)$_SESSION['userId'];
$role = $_SESSION['userRole'];

if (!$id) {
    maintenance_redirect('dashboard/maintenance/', 'error', 'ID laporan tidak valid.');
}

$report = fetchMaintenanceReportById($db, $id);

if (!$report) {
    maintenance_redirect('dashboard/maintenance/', 'error', 'Laporan tidak ditemukan.');
}

// Validation: Only allow deletion if status is still "Diajukan"
if ($report['StatusMaintenance'] !== 'Diajukan') {
    maintenance_redirect('dashboard/maintenance/', 'error', 'Laporan yang sedang diproses atau sudah selesai tidak dapat dihapus.');
}

// Ownership verification
$isOwner = isMaintenanceReportOwner($report, $role, $userId);

if (!$isOwner) {
    maintenance_redirect('dashboard/maintenance/', 'error', 'Anda tidak memiliki wewenang untuk menghapus laporan ini.');
}

try {
    deleteMaintenanceReport($db, $id);
    maintenance_redirect('dashboard/maintenance/', 'success', 'Laporan kerusakan berhasil dihapus.');
} catch (RuntimeException) {
    maintenance_redirect('dashboard/maintenance/', 'error', 'Gagal menghapus laporan.');
}
