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
    header("Location: /doremi-app/dashboard/inventaris/");
    exit;
}
csrf_validate('/doremi-app/dashboard/inventaris/');

require '../../db.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = mysqli_prepare($db, "UPDATE inventaris SET IsDeleted = 1 WHERE InventarisID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /doremi-app/dashboard/inventaris/?status=success&message=Inventaris Berhasil Dihapus!");
    } else {
        header("Location: /doremi-app/dashboard/inventaris/?status=error&message=Gagal Menghapus Inventaris!");
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: /doremi-app/dashboard/inventaris/");
}
exit;
