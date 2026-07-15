<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchAllInventarisWithLokasi(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getAllInventarisWithLokasi()");
}

function fetchInventarisByKamar(mysqli $db, int $kamarId): array
{
    return dbFetchAll($db, "CALL sp_getInventarisByKamar(?)", 'i', [$kamarId]);
}

function fetchActiveKamarOptions(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getActiveKamarOptions()");
}

function fetchActiveRuanganOptions(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getActiveRuanganOptions()");
}

function checkKamarActive(mysqli $db, int $kamarId): bool
{
    return dbFetchOne($db, "CALL sp_checkKamarActive(?)", 'i', [$kamarId]) !== null;
}

function checkRuanganActive(mysqli $db, int $ruanganId): bool
{
    return dbFetchOne($db, "CALL sp_checkRuanganActive(?)", 'i', [$ruanganId]) !== null;
}

function fetchInventarisById(mysqli $db, int $id): ?array
{
    return dbFetchOne($db, "CALL sp_getInventarisById(?)", 'i', [$id]);
}

function createInventaris(mysqli $db, ?int $ruanganId, ?int $kamarId, string $nama, int $jumlah, string $keterangan): bool
{
    dbExecute($db, "CALL sp_createInventaris(?, ?, ?, ?, ?)", 'iisis', [$ruanganId, $kamarId, $nama, $jumlah, $keterangan]);
    return true;
}

function updateInventaris(mysqli $db, int $id, ?int $ruanganId, ?int $kamarId, string $nama, int $jumlah, string $keterangan): bool
{
    dbExecute($db, "CALL sp_updateInventaris(?, ?, ?, ?, ?, ?)", 'iiisis', [$id, $ruanganId, $kamarId, $nama, $jumlah, $keterangan]);
    return true;
}

function deleteInventaris(mysqli $db, int $id): bool
{
    dbExecute($db, "CALL sp_deleteInventaris(?)", 'i', [$id]);
    return true;
}
