<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPaketReportExport(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportExport(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportStats(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchOne($db, "CALL sp_getPaketReportStats(?, ?, ?)", 'sss', [$range, $startDate, $endDate]) ?? [];
}

function fetchPaketReportStatusDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportStatusDist(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportTrendDaily(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTrendDaily(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportTrendMonthly(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTrendMonthly(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportTipeDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTipeDist(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportTopKurir(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTopKurir(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportJamSibuk(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportJamSibuk(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportTopPenghuni(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTopPenghuni(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportPetugasRanking(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportPetugasRanking(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchPaketReportDetail(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportDetail(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}
