<?php
require_once '../../utils/url.php';
session_start();

if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}

require '../../db.php';
require '../../database/petugas.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        deletePetugas($db, (int) $id);
        header('Location: ' . app_url('dashboard/petugas/?status=success&message=Petugas Berhasil Dihapus!'));
    } catch (RuntimeException $e) {
        header('Location: ' . app_url('dashboard/petugas/?status=error&message=Gagal Menghapus Petugas!'));
    }
} else {
    header('Location: ' . app_url('dashboard/petugas/'));
}
exit;
