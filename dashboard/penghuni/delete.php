<?php
require_once '../../utils/url.php';
session_start();

if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}

require '../../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = mysqli_prepare($db, "UPDATE penghuni SET IsDeleted = 1 WHERE PenghuniID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: ' . app_url('dashboard/penghuni/?status=success&message=Penghuni Berhasil Dihapus!'));
    } else {
        header('Location: ' . app_url('dashboard/penghuni/?status=error&message=Gagal Menghapus Penghuni!'));
    }
    mysqli_stmt_close($stmt);
} else {
    header('Location: ' . app_url('dashboard/penghuni/'));
}
exit;
