<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchInOutReportExport(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportExport(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportStats(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchOne($db, "CALL sp_getInOutReportStats(?, ?, ?)", 'sss', [$range, $startDate, $endDate]) ?? [];
}

function fetchInOutReportStatusDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportStatusDist(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportTrendDaily(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTrendDaily(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportTrendMonthly(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTrendMonthly(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportPeakHour(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportPeakHour(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportTopPenghuni(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTopPenghuni(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportGenderDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportGenderDist(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportTopKeperluan(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportTopKeperluan(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportPetugasRanking(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportPetugasRanking(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchInOutReportDetail(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getInOutReportDetail(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}
