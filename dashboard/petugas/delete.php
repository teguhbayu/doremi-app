<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';
require '../../database/petugas.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        deletePetugas($db, (int) $id);
        header("Location: /doremi-app/dashboard/petugas/?status=success&message=Petugas Berhasil Dihapus!");
    } catch (RuntimeException $e) {
        header("Location: /doremi-app/dashboard/petugas/?status=error&message=Gagal Menghapus Petugas!");
    }
} else {
    header("Location: /doremi-app/dashboard/petugas/");
}
exit;
