<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/maintenance.php';
require_once __DIR__ . '/paket.php';

function fetchPhotoDataByType(mysqli $db, string $type, int $id): ?string
{
    return match ($type) {
        'maintenance_laporan', 'maintenance_perbaikan' => fetchMaintenancePhoto($db, $type, $id),
        'paket_pengambilan' => fetchPaketPickupPhoto($db, $id),
        default => null,
    };
}
