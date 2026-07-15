<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function createRuangan(mysqli $db, string $nama, string $jenis, string $lantai, string $keterangan): bool
{
    dbExecute($db, 'INSERT INTO ruangan (NamaRuangan, JenisRuangan, Lantai, Keterangan, IsDeleted) VALUES (?, ?, ?, ?, 0)', 'ssss', [$nama, $jenis, $lantai, $keterangan]);
    return true;
}

function updateRuangan(mysqli $db, int $id, string $nama, string $jenis, string $lantai, string $keterangan): bool
{
    dbExecute($db, 'UPDATE ruangan SET NamaRuangan = ?, JenisRuangan = ?, Lantai = ?, Keterangan = ? WHERE RuanganID = ?', 'ssssi', [$nama, $jenis, $lantai, $keterangan, $id]);
    return true;
}
