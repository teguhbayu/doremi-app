<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchAllInventarisWithLokasi(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT i.*, k.NomorKamar, r.NamaRuangan FROM inventaris i LEFT JOIN kamar k ON i.KamarID = k.KamarID LEFT JOIN ruangan r ON i.RuanganID = r.RuanganID WHERE i.IsDeleted = 0');
}

function fetchInventarisByKamar(mysqli $db, int $kamarId): array
{
    return dbFetchAll($db, 'SELECT InventarisID, NamaBarang, Jumlah, Keterangan, UpdatedAt FROM inventaris WHERE KamarID = ? AND IsDeleted = 0 ORDER BY NamaBarang ASC', 'i', [$kamarId]);
}

function fetchActiveKamarOptions(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT KamarID, NomorKamar FROM kamar WHERE IsDeleted = 0');
}

function fetchActiveRuanganOptions(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT RuanganID, NamaRuangan FROM ruangan WHERE IsDeleted = 0');
}

function checkKamarActive(mysqli $db, int $kamarId): bool
{
    return dbFetchOne($db, 'SELECT KamarID FROM kamar WHERE KamarID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$kamarId]) !== null;
}

function checkRuanganActive(mysqli $db, int $ruanganId): bool
{
    return dbFetchOne($db, 'SELECT RuanganID FROM ruangan WHERE RuanganID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$ruanganId]) !== null;
}

function fetchInventarisById(mysqli $db, int $id): ?array
{
    return dbFetchOne($db, 'SELECT * FROM inventaris WHERE InventarisID = ? LIMIT 1', 'i', [$id]);
}

function createInventaris(mysqli $db, ?int $ruanganId, ?int $kamarId, string $nama, int $jumlah, string $keterangan): bool
{
    dbExecute($db, 'INSERT INTO inventaris (RuanganID, KamarID, NamaBarang, Jumlah, Keterangan, IsDeleted) VALUES (?, ?, ?, ?, ?, 0)', 'iisis', [$ruanganId, $kamarId, $nama, $jumlah, $keterangan]);
    return true;
}

function updateInventaris(mysqli $db, int $id, ?int $ruanganId, ?int $kamarId, string $nama, int $jumlah, string $keterangan): bool
{
    dbExecute($db, 'UPDATE inventaris SET RuanganID = ?, KamarID = ?, NamaBarang = ?, Jumlah = ?, Keterangan = ? WHERE InventarisID = ?', 'iisisi', [$ruanganId, $kamarId, $nama, $jumlah, $keterangan, $id]);
    return true;
}

function deleteInventaris(mysqli $db, int $id): bool
{
    dbExecute($db, 'UPDATE inventaris SET IsDeleted = 1 WHERE InventarisID = ?', 'i', [$id]);
    return true;
}
