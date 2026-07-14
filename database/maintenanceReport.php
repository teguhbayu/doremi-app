<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchMaintenanceReportExport(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportExport(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportStats(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchOne($db, "CALL sp_getMaintenanceReportStats(?, ?, ?)", 'sss', [$range, $startDate, $endDate]) ?? [];
}

function fetchMaintenanceReportPriorityDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportPriorityDist(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportTrendDaily(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportTrendDaily(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportTrendMonthly(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportTrendMonthly(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportTopRuangan(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportTopRuangan(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportStackedStatus(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportStackedStatus(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportPetugasRanking(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportPetugasRanking(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}

function fetchMaintenanceReportDetail(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportDetail(?, ?, ?)", 'sss', [$range, $startDate, $endDate]);
}
