<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function collectPaketInput(array $source): array
{
    return [
        'jenisPaket' => paket_normalize_type($source['jenisPaket'] ?? null),
        'namaPengirim' => trim($source['namaPengirim'] ?? ''),
        'kurir' => trim($source['kurir'] ?? ''),
        'penghuniId' => filter_input(INPUT_POST, 'penghuniId', FILTER_VALIDATE_INT),
        'waktuSampai' => paket_normalize_datetime($source['waktuSampai'] ?? ''),
    ];
}

function validatePaketInput(mysqli $db, array $input): ?string
{
    if ($input['jenisPaket'] === null) {
        return 'Kolom Tipe Kiriman wajib diisi.';
    }

    if (!paket_is_valid_length($input['namaPengirim'], 1, 100)) {
        return $input['namaPengirim'] === '' ? 'Kolom Nama Pengirim wajib diisi.' : 'Kolom Nama Pengirim tidak valid.';
    }

    if (!paket_is_valid_length($input['kurir'], 1, 50)) {
        return $input['kurir'] === '' ? 'Kolom Kurir wajib diisi.' : 'Kolom Kurir tidak valid.';
    }

    if ($input['penghuniId'] === false || $input['penghuniId'] === null) {
        return 'Kolom Penghuni Tujuan wajib diisi.';
    }

    if ($input['waktuSampai'] === null) {
        return 'Kolom Waktu Sampai wajib diisi.';
    }

    if (!checkPenghuniExists($db, (int) $input['penghuniId'])) {
        return 'Penghuni tujuan tidak ditemukan.';
    }

    return null;
}

function collectPaketReviewInput(array $source): array
{
    return [
        'status' => trim($source['status'] ?? ''),
        'keterangan' => trim($source['keterangan'] ?? ''),
    ];
}

function validatePaketReviewInput(array $input, int $petugasId): ?string
{
    if ($petugasId < 1 || !in_array($input['status'], ['Sudah Diambil', 'TERTUKAR'], true)) {
        return 'Status review paket tidak valid.';
    }

    return null;
}
