<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchInOutHistoryForPenghuni(mysqli $db, int $penghuniId): array
{
    return dbFetchAll($db, "CALL sp_getInOutHistoryForPenghuni(?)", 'i', [$penghuniId]);
}

function countActiveInOutRequests(mysqli $db, int $penghuniId): int
{
    return (int) dbFetchValue($db, "CALL sp_countActiveInOutRequests(?)", 'i', [$penghuniId], 'count');
}

function fetchPendingInOutRequests(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getPendingInOutRequests()");
}

function fetchOutsideInOutRequests(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getOutsideInOutRequests()");
}

function fetchAllInOutLogs(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getAllInOutLogs()");
}

function createInOutRequest(mysqli $db, int $penghuniId, string $keperluan, string $waktuKeluar, string $waktuMasuk): bool
{
    dbExecute(
        $db,
        "CALL sp_createInOutRequest(?, ?, ?, ?)",
        'isss',
        [$penghuniId, $keperluan, $waktuKeluar, $waktuMasuk]
    );

    return true;
}

function confirmInOutExit(mysqli $db, int $inOutId, string $waktuKeluar, int $petugasId): bool
{
    dbExecute($db, "CALL sp_confirmInOutExit(?, ?, ?)", 'isi', [$inOutId, $waktuKeluar, $petugasId]);
    return true;
}

function confirmInOutEntry(mysqli $db, int $inOutId, string $waktuMasuk): bool
{
    dbExecute($db, "CALL sp_confirmInOutEntry(?, ?)", 'is', [$inOutId, $waktuMasuk]);
    return true;
}
