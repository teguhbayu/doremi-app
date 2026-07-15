<?php
require_once '../../utils/url.php';
session_start();

if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}

require '../../db.php';
require_once '../../database/kamar.php';

$id = $_GET['id'] ?? null;

if ($id) {
    if (countActivePenghuniByKamar($db, (int) $id) > 0) {
        header('Location: ' . app_url('dashboard/kamar/?status=error&message=Kamar tidak dapat dihapus karena masih memiliki penghuni!'));
        exit;
    }

    $stmt = mysqli_prepare($db, "UPDATE kamar SET IsDeleted = 1 WHERE KamarID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: ' . app_url('dashboard/kamar/?status=success&message=Kamar Berhasil Dihapus!'));
    } else {
        header('Location: ' . app_url('dashboard/kamar/?status=error&message=Gagal Menghapus Kamar!'));
    }
    mysqli_stmt_close($stmt);
} else {
    header('Location: ' . app_url('dashboard/kamar/'));
}
exit;
