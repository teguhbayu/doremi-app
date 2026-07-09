<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchMaintenanceReportExport(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportExport(?)", 's', [$range]);
}

function fetchMaintenanceReportStats(mysqli $db, string $range): array
{
    return dbFetchOne($db, "CALL sp_getMaintenanceReportStats(?)", 's', [$range]) ?? [];
}

function fetchMaintenanceReportPriorityDist(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportPriorityDist(?)", 's', [$range]);
}

function fetchMaintenanceReportTrendDaily(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportTrendDaily(?)", 's', [$range]);
}

function fetchMaintenanceReportTrendMonthly(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportTrendMonthly(?)", 's', [$range]);
}

function fetchMaintenanceReportTopRuangan(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportTopRuangan(?)", 's', [$range]);
}

function fetchMaintenanceReportStackedStatus(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportStackedStatus(?)", 's', [$range]);
}

function fetchMaintenanceReportPetugasRanking(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportPetugasRanking(?)", 's', [$range]);
}

function fetchMaintenanceReportDetail(mysqli $db, string $range): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportDetail(?)", 's', [$range]);
}
