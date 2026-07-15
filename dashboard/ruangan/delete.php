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
    header('Location: ' . app_url('dashboard/ruangan/'));
    exit;
}
    csrf_validate(app_url('dashboard/ruangan/'));

require '../../db.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = mysqli_prepare($db, "UPDATE ruangan SET IsDeleted = 1 WHERE RuanganID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
    header('Location: ' . app_url('dashboard/ruangan/?status=success&message=Ruangan Berhasil Dihapus!'));
    } else {
    header('Location: ' . app_url('dashboard/ruangan/?status=error&message=Gagal Menghapus Ruangan!'));
    }
    mysqli_stmt_close($stmt);
} else {
    header('Location: ' . app_url('dashboard/ruangan/'));
}
exit;
