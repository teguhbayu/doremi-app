<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchDashboardPengurusStats(mysqli $db): array
{
    return dbFetchOne($db, "CALL sp_getDashboardPengurusStats()") ?? [
        'activePenghuni' => 0,
        'pendingInOut' => 0,
        'pendingMaintenance' => 0,
        'pendingPackagePickup' => 0,
    ];
}

function fetchDashboardGenderStats(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getDashboardGenderStats()");
}

function fetchDashboardPenghuniIzinAktif(mysqli $db, int $userId): int
{
    return (int) dbFetchValue($db, "CALL sp_getDashboardPenghuniIzinAktif(?)", 'i', [$userId]);
}

function fetchDashboardPenghuniPaketSummary(mysqli $db, int $userId): array
{
    return dbFetchAll($db, "CALL sp_getDashboardPenghuniPaketSummary(?)", 'i', [$userId]);
}

function fetchDashboardPenghuniMaintenanceSummary(mysqli $db, int $userId): array
{
    return dbFetchAll($db, "CALL sp_getDashboardPenghuniMaintenanceSummary(?)", 'i', [$userId]);
}

function fetchDashboardMaintenanceCounts(mysqli $db, int $userId): array
{
    return dbFetchOne($db, "CALL sp_getDashboardMaintenanceCounts(?)", 'i', [$userId]) ?? [
        'pendingTasks' => 0,
        'myOngoingTasks' => 0,
        'myCompletedTasks' => 0,
        'activeEmergencyTasks' => 0,
    ];
}

function fetchDashboardMyTasks(mysqli $db, int $userId): array
{
    return dbFetchAll($db, "CALL sp_getDashboardMyTasks(?)", 'i', [$userId]);
}

function fetchDashboardEmergencyList(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getDashboardEmergencyList()");
}

function fetchMaintenanceStatusPie(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceStatusPie()");
}

function fetchMaintenanceTrendDaily(mysqli $db, int $intervalDays): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceTrendDaily(?)", 'i', [$intervalDays]);
}

function fetchMaintenanceTrendMonthly(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceTrendMonthly()");
}
