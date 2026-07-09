<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPaketReportExport(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportExport(?)", 's', [$range]);
}

function fetchPaketReportStats(mysqli $db, string $range): array
{
    return dbFetchOne($db, "CALL sp_getPaketReportStats(?)", 's', [$range]) ?? [];
}

function fetchPaketReportStatusDist(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportStatusDist(?)", 's', [$range]);
}

function fetchPaketReportTrendDaily(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTrendDaily(?)", 's', [$range]);
}

function fetchPaketReportTrendMonthly(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTrendMonthly(?)", 's', [$range]);
}

function fetchPaketReportTipeDist(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTipeDist(?)", 's', [$range]);
}

function fetchPaketReportTopKurir(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTopKurir(?)", 's', [$range]);
}

function fetchPaketReportJamSibuk(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportJamSibuk(?)", 's', [$range]);
}

function fetchPaketReportTopPenghuni(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportTopPenghuni(?)", 's', [$range]);
}

function fetchPaketReportPetugasRanking(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportPetugasRanking(?)", 's', [$range]);
}

function fetchPaketReportDetail(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getPaketReportDetail(?)", 's', [$range]);
}
