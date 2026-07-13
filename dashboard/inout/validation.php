<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function collectInOutRequestInput(array $source): array
{
    return [
        'keperluan' => trim($source['keperluan'] ?? ''),
        'waktuKeluarTime' => $source['waktuKeluar'] ?? '',
        'waktuMasukTime' => $source['waktuMasuk'] ?? '',
    ];
}

function validateInOutRequestInput(array $input): ?string
{
    if (empty($input['waktuKeluarTime'])) {
        return 'Kolom Rencana Keluar wajib diisi.';
    }

    if (empty($input['waktuMasukTime'])) {
        return 'Kolom Rencana Masuk wajib diisi.';
    }

    if (empty($input['keperluan'])) {
        return 'Kolom Keperluan wajib diisi.';
    }

    if (textLength($input['keperluan']) > 20) {
        return 'Keperluan maksimal 20 karakter!';
    }

    $currentTime = date('H:i');
    $maxTime = '22:00';

    if ($input['waktuKeluarTime'] < $currentTime || $input['waktuKeluarTime'] > $maxTime) {
        return 'Waktu keluar harus antara sekarang dan 22:00!';
    }

    if ($input['waktuMasukTime'] < $currentTime || $input['waktuMasukTime'] > $maxTime) {
        return 'Waktu masuk harus antara sekarang dan 22:00!';
    }

    if ($input['waktuMasukTime'] <= $input['waktuKeluarTime']) {
        return 'Waktu masuk harus setelah waktu keluar!';
    }

    return null;
}

function buildInOutDateTimes(array $input): array
{
    $today = date('Y-m-d');

    return [
        'waktuKeluar' => $today . ' ' . $input['waktuKeluarTime'] . ':00',
        'waktuMasuk' => $today . ' ' . $input['waktuMasukTime'] . ':00',
    ];
}
