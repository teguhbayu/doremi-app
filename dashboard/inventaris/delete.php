<?php
require_once '../../utils/url.php';
session_start();

if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}
if ($_SESSION['userRole'] !== 'PENGURUS') {
    app_redirect('dashboard/');
}
require '../../csrf.php';

// Only accept POST to prevent CSRF via URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . app_url('dashboard/inventaris/'));
    exit;
}
    csrf_validate(app_url('dashboard/inventaris/'));

require '../../db.php';
require '../../database/inventaris.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        deleteInventaris($db, (int) $id);
    header('Location: ' . app_url('dashboard/inventaris/?status=success&message=Inventaris Berhasil Dihapus!'));
    } catch (RuntimeException $e) {
    header('Location: ' . app_url('dashboard/inventaris/?status=error&message=Gagal Menghapus Inventaris!'));
    }
} else {
    header('Location: ' . app_url('dashboard/inventaris/'));
}
exit;
