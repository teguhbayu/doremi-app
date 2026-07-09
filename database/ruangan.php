<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function createRuangan(mysqli $db, string $nama, string $jenis, string $lantai, string $keterangan): bool
{
    dbExecute($db, "CALL sp_createRuangan(?, ?, ?, ?)", 'ssss', [$nama, $jenis, $lantai, $keterangan]);
    return true;
}

function updateRuangan(mysqli $db, int $id, string $nama, string $jenis, string $lantai, string $keterangan): bool
{
    dbExecute($db, "CALL sp_updateRuangan(?, ?, ?, ?, ?)", 'issss', [$id, $nama, $jenis, $lantai, $keterangan]);
    return true;
}
