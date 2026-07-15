<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';
require_once '../../database/kamar.php';

$id = $_GET['id'] ?? null;

if ($id) {
    if (countActivePenghuniByKamar($db, (int) $id) > 0) {
        header("Location: /doremi-app/dashboard/kamar/?status=error&message=Kamar tidak dapat dihapus karena masih memiliki penghuni!");
        exit;
    }

    $stmt = mysqli_prepare($db, "CALL sp_deleteKamar(?)");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /doremi-app/dashboard/kamar/?status=success&message=Kamar Berhasil Dihapus!");
    } else {
        header("Location: /doremi-app/dashboard/kamar/?status=error&message=Gagal Menghapus Kamar!");
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: /doremi-app/dashboard/kamar/");
}
exit;
