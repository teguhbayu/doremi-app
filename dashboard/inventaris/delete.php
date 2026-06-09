<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';

$id = $_GET['id'] ?? null;

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
