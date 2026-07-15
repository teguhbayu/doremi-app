<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP']);
require '../../db.php';
require_once '../../database/paket.php';

$paketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paketId) {
        paket_redirect('dashboard/paket/', 'error', 'Data paket tidak valid.');
}

if (countPackagePickupsByPaketId($db, $paketId) > 0) {
        paket_redirect('dashboard/paket/', 'error', 'Paket yang sudah memiliki catatan pengambilan tidak dapat dihapus.');
}

if (!fetchPaketDetail($db, $paketId)) {
        paket_redirect('dashboard/paket/', 'error', 'Data paket tidak ditemukan atau gagal dihapus.');
}

try {
    deletePaket($db, $paketId);
} catch (RuntimeException) {
        paket_redirect('dashboard/paket/', 'error', 'Data paket tidak ditemukan atau gagal dihapus.');
}

    paket_redirect('dashboard/paket/', 'success', 'Data paket berhasil dihapus.');
