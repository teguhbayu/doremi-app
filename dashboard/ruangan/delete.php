<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
if ($_SESSION['userRole'] !== 'PENGURUS') {
    header("Location: /doremi-app/dashboard/");
    exit;
}
require '../../csrf.php';

// Only accept POST to prevent CSRF via URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /doremi-app/dashboard/ruangan/");
    exit;
}
csrf_validate('/doremi-app/dashboard/ruangan/');

require '../../db.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = mysqli_prepare($db, "UPDATE ruangan SET IsDeleted = 1 WHERE RuanganID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /doremi-app/dashboard/ruangan/?status=success&message=Ruangan Berhasil Dihapus!");
    } else {
        header("Location: /doremi-app/dashboard/ruangan/?status=error&message=Gagal Menghapus Ruangan!");
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: /doremi-app/dashboard/ruangan/");
}
exit;
