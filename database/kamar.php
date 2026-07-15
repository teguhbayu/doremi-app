<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchKamarById(mysqli $db, int $id): ?array
{
    return dbFetchOne($db, 'SELECT * FROM kamar WHERE KamarID = ? LIMIT 1', 'i', [$id]);
}

function countActivePenghuniByKamar(mysqli $db, int $kamarId): int
{
    return (int) dbFetchValue($db, 'SELECT COUNT(*) AS total FROM penghuni WHERE KamarID = ? AND IsDeleted = 0', 'i', [$kamarId], 'total');
}

function findKamarDuplicateNomor(mysqli $db, string $nomor, int $excludeId = 0): ?array
{
    return dbFetchOne($db, "SELECT KamarID FROM kamar WHERE IsDeleted = 0 AND UPPER(REPLACE(NomorKamar, ' ', '')) = ? AND (? = 0 OR KamarID != ?) LIMIT 1", 'sii', [$nomor, $excludeId, $excludeId]);
}

function createKamar(mysqli $db, string $nomor, int $kapasitas, string $lantai): bool
{
    dbExecute($db, 'INSERT INTO kamar (NomorKamar, KapasitasPenghuni, Lantai, IsDeleted) VALUES (?, ?, ?, 0)', 'sis', [$nomor, $kapasitas, $lantai]);
    return true;
}

function updateKamar(mysqli $db, int $id, string $nomor, int $kapasitas, string $lantai): bool
{
    dbExecute($db, 'UPDATE kamar SET NomorKamar = ?, KapasitasPenghuni = ?, Lantai = ? WHERE KamarID = ?', 'sisi', [$nomor, $kapasitas, $lantai, $id]);
    return true;
}
