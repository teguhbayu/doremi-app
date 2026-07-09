<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchInOutReportExport(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportExport(?)", 's', [$range]);
}

function fetchInOutReportStats(mysqli $db, string $range): array
{
    return dbFetchOne($db, "CALL sp_getInOutReportStats(?)", 's', [$range]) ?? [];
}

function fetchInOutReportStatusDist(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportStatusDist(?)", 's', [$range]);
}

function fetchInOutReportTrendDaily(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTrendDaily(?)", 's', [$range]);
}

function fetchInOutReportTrendMonthly(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTrendMonthly(?)", 's', [$range]);
}

function fetchInOutReportPeakHour(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportPeakHour(?)", 's', [$range]);
}

function fetchInOutReportTopPenghuni(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTopPenghuni(?)", 's', [$range]);
}

function fetchInOutReportGenderDist(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportGenderDist(?)", 's', [$range]);
}

function fetchInOutReportTopKeperluan(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTopKeperluan(?)", 's', [$range]);
}

function fetchInOutReportPetugasRanking(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportPetugasRanking(?)", 's', [$range]);
}

function fetchInOutReportDetail(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportDetail(?)", 's', [$range]);
}
