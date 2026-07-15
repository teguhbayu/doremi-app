<?php
session_start();
require 'helpers.php';

paket_require_roles(['SIGAP']);
require '../../csrf.php';
csrf_validate('dashboard/paket/');

require '../../db.php';
require_once '../../database/paket.php';
require 'validation.php';

$action = $_POST['action'] ?? '';
$paketId = filter_input(INPUT_POST, 'paketId', FILTER_VALIDATE_INT);
$userId = (int) $_SESSION['userId'];

if (!$paketId) {
    paket_redirect('dashboard/paket/index.php', 'error', 'Data paket tidak valid.');
}

$paket = fetchPaketWithLatestPickup($db, $paketId, 0);

if (!$paket) {
    paket_redirect('dashboard/paket/index.php', 'error', 'Data paket tidak ditemukan.');
}

if ($action === 'updateStatus') {
    if (empty($paket['PengambilanPaketID']) || ($paket['Status'] ?? '') !== 'Sudah Diambil') {
        paket_redirect('dashboard/paket/index.php', 'error', 'Status paket hanya bisa diubah setelah paket ditandai Sudah Diambil.');
    }

    $reviewInput = collectPaketReviewInput($_POST);
    $validationMessage = validatePaketReviewInput($reviewInput, $userId);
    if ($validationMessage !== null) {
        paket_redirect('dashboard/paket/index.php', 'error', $validationMessage);
    }

    $keterangan = $reviewInput['keterangan'] !== '' ? $reviewInput['keterangan'] : '-';

    try {
        updatePaketPickupReview($db, (int) $paket['PengambilanPaketID'], $userId, $reviewInput['status'], $keterangan);
        paket_redirect('dashboard/paket/index.php', 'success', 'Status paket berhasil diperbarui.');
    } catch (RuntimeException) {
        paket_redirect('dashboard/paket/index.php', 'error', 'Terjadi kesalahan sistem saat memperbarui status.');
    }
} else {
    paket_redirect('dashboard/paket/index.php');
}
