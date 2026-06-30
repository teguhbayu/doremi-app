<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = mysqli_prepare($db, "CALL sp_deleteRuangan(?)");
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
