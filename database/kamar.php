<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchKamarById(mysqli $db, int $id): ?array
{
    return dbFetchOne($db, "CALL sp_getKamarById(?)", 'i', [$id]);
}

function countActivePenghuniByKamar(mysqli $db, int $kamarId): int
{
    return (int) dbFetchValue($db, "CALL sp_countActivePenghuniByKamar(?)", 'i', [$kamarId], 'total');
}

function findKamarDuplicateNomor(mysqli $db, string $nomor, int $excludeId = 0): ?array
{
    return dbFetchOne($db, "CALL sp_findKamarDuplicateNomor(?, ?)", 'si', [$nomor, $excludeId]);
}

function createKamar(mysqli $db, string $nomor, int $kapasitas, string $lantai): bool
{
    dbExecute($db, "CALL sp_createKamar(?, ?, ?)", 'sis', [$nomor, $kapasitas, $lantai]);
    return true;
}

function updateKamar(mysqli $db, int $id, string $nomor, int $kapasitas, string $lantai): bool
{
    dbExecute($db, "CALL sp_updateKamar(?, ?, ?, ?)", 'isis', [$id, $nomor, $kapasitas, $lantai]);
    return true;
}
