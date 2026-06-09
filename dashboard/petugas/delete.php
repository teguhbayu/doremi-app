<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = mysqli_prepare($db, "UPDATE petugas SET IsDeleted = 1 WHERE PetugasID = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: /doremi-app/dashboard/petugas/?status=success&message=Petugas Berhasil Dihapus!");
    } else {
        header("Location: /doremi-app/dashboard/petugas/?status=error&message=Gagal Menghapus Petugas!");
    }
    mysqli_stmt_close($stmt);
} else {
    header("Location: /doremi-app/dashboard/petugas/");
}
exit;
