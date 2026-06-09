<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = mysqli_prepare($db, "UPDATE penghuni SET IsDeleted = 1 WHERE PenghuniID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /doremi-app/dashboard/penghuni/?status=success&message=Penghuni Berhasil Dihapus!");
    } else {
        header("Location: /doremi-app/dashboard/penghuni/?status=error&message=Gagal Menghapus Penghuni!");
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: /doremi-app/dashboard/penghuni/");
}
exit;
