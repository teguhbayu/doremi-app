<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchMaintenanceReportsForRole(mysqli $db, string $role, int $userId): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceReportsForRole(?, ?)", 'si', [$role, $userId]);
}

function fetchMaintenanceReportById(mysqli $db, int $maintenanceId): ?array
{
    return dbFetchOne($db, "CALL sp_getMaintenanceReportById(?)", 'i', [$maintenanceId]);
}

function fetchMaintenanceRooms(mysqli $db, bool $onlyActive = true): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceRooms(?)", 'i', [$onlyActive ? 1 : 0]);
}

function fetchMaintenanceInventory(mysqli $db, bool $onlyActive = true): array
{
    return dbFetchAll($db, "CALL sp_getMaintenanceInventory(?)", 'i', [$onlyActive ? 1 : 0]);
}

function checkMaintenanceTargetExists(mysqli $db, string $targetType, int $targetId): bool
{
    if ($targetType === 'ruangan') {
        return dbFetchOne($db, "CALL sp_checkRuanganActive(?)", 'i', [$targetId]) !== null;
    }
    if ($targetType === 'kamar') {
        return dbFetchOne($db, "CALL sp_checkKamarActive(?)", 'i', [$targetId]) !== null;
    }

    return dbFetchOne($db, "CALL sp_checkInventarisActive(?)", 'i', [$targetId]) !== null;
}

function createMaintenanceReport(
    mysqli $db,
    ?int $penghuniId,
    ?int $petugasId,
    ?int $ruanganId,
    ?int $inventarisId,
    ?int $kamarId,
    string $jenisLaporan,
    string $deskripsi,
    string $fotoLaporan,
    string $tanggalLapor
): bool {
    dbExecute(
        $db,
        "CALL sp_createMaintenanceReport(?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'iiiiissss',
        [$penghuniId, $petugasId, $ruanganId, $inventarisId, $kamarId, $jenisLaporan, $deskripsi, $fotoLaporan, $tanggalLapor]
    );

    return true;
}

function updateMaintenanceReport(
    mysqli $db,
    int $maintenanceId,
    ?int $ruanganId,
    ?int $inventarisId,
    ?int $kamarId,
    string $jenisLaporan,
    string $deskripsi,
    string $fotoLaporan
): bool {
    dbExecute(
        $db,
        "CALL sp_updateMaintenanceReport(?, ?, ?, ?, ?, ?, ?)",
        'iiiisss',
        [$maintenanceId, $ruanganId, $inventarisId, $kamarId, $jenisLaporan, $deskripsi, $fotoLaporan]
    );

    return true;
}

function updateMaintenanceUrgency(mysqli $db, int $maintenanceId, string $jenisLaporan): bool
{
    dbExecute(
        $db,
        "CALL sp_updateMaintenanceUrgency(?, ?)",
        'is',
        [$maintenanceId, $jenisLaporan]
    );

    return true;
}

function claimMaintenanceReport(mysqli $db, int $maintenanceId, int $petugasId): int
{
    return dbExecute($db, "CALL sp_claimMaintenanceReport(?, ?)", 'ii', [$petugasId, $maintenanceId]);
}

function checkMaintenanceTechnicianOwnership(mysqli $db, int $maintenanceId, int $petugasId): bool
{
    return dbFetchOne($db, "CALL sp_checkMaintenanceTechnicianOwnership(?, ?)", 'ii', [$maintenanceId, $petugasId]) !== null;
}

function completeMaintenanceReport(mysqli $db, int $maintenanceId, string $tanggalSelesai, string $keterangan, string $fotoMaintenance): bool
{
    dbExecute(
        $db,
        "CALL sp_completeMaintenanceReport(?, ?, ?, ?)",
        'sssi',
        [$tanggalSelesai, $keterangan, $fotoMaintenance, $maintenanceId]
    );

    return true;
}

function deleteMaintenanceReport(mysqli $db, int $maintenanceId): bool
{
    dbExecute($db, "CALL sp_deleteMaintenanceReport(?)", 'i', [$maintenanceId]);
    return true;
}

function isMaintenanceReportOwner(array $report, string $role, int $userId): bool
{
    if ($role === 'PENGHUNI') {
        return (int) $report['PenghuniID'] === $userId;
    }

    return (int) $report['PetugasID'] === $userId && $report['PenghuniID'] === null;
}

function fetchMaintenancePhoto(mysqli $db, string $photoType, int $maintenanceId): ?string
{
    $procedure = $photoType === 'maintenance_perbaikan'
        ? 'sp_getFotoMaintenanceFromMaintenance'
        : 'sp_getFotoLaporanFromMaintenance';

    $row = dbFetchOne($db, "CALL $procedure(?)", 'i', [$maintenanceId]);
    if ($row === null) {
        return null;
    }

    return reset($row) ?: null;
}
