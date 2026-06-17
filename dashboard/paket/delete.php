<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP']);
require '../../db.php';

$paketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paketId) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak valid.');
}

$stmt = mysqli_prepare($db, "SELECT COUNT(*) AS total FROM pengambilanpaket WHERE PaketID = ?");
mysqli_stmt_bind_param($stmt, 'i', $paketId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pengambilan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (($pengambilan['total'] ?? 0) > 0) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Paket yang sudah memiliki catatan pengambilan tidak dapat dihapus.');
}

$stmt = mysqli_prepare($db, "DELETE FROM paket WHERE PaketID = ?");
mysqli_stmt_bind_param($stmt, 'i', $paketId);
mysqli_stmt_execute($stmt);
$affectedRows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($affectedRows < 1) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak ditemukan atau gagal dihapus.');
}

paket_redirect('/doremi-app/dashboard/paket/', 'success', 'Data paket berhasil dihapus.');
