<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchMaintenanceReportsForRole(mysqli $db, string $role, int $userId): array
{
    $where = 'm.IsDeleted = 0';
    $types = '';
    $params = [];

    if ($role !== 'MAINTENANCE') {
        if ($role === 'PENGHUNI') {
            $where .= ' AND m.PenghuniID = ?';
        } else {
            $where .= ' AND m.PetugasID = ? AND m.PenghuniID IS NULL';
        }
        $types = 'i';
        $params[] = $userId;
    }

    return dbFetchAll(
        $db,
        "SELECT m.MaintenanceID, m.PenghuniID, m.PetugasID, m.RuanganID, m.InventarisID,
                m.TanggalLapor, m.JenisLaporan, m.Deskripsi, m.StatusMaintenance,
                m.TanggalSelesai, m.Keterangan,
                (m.FotoLaporan IS NOT NULL AND m.FotoLaporan != '') AS HasFotoLaporan,
                (m.FotoMaintenance IS NOT NULL AND m.FotoMaintenance != '') AS HasFotoMaintenance,
                p.NamaPenghuni, p.Nim,
                pt.NamaPetugas AS NamaReporterPetugas,
                tech.NamaPetugas AS NamaTeknisi,
                r.NamaRuangan, r.Lantai AS LantaiRuangan,
                i.NamaBarang
         FROM maintenance m
         LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
         LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
         LEFT JOIN petugas tech ON m.PetugasID = tech.PetugasID
         LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
         LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
         WHERE $where
         ORDER BY CASE WHEN m.JenisLaporan = 'Kerusakan Darurat / Berat' THEN 1
                       WHEN m.JenisLaporan = 'Kerusakan Sedang' THEN 2
                       ELSE 3 END, m.MaintenanceID DESC",
        $types,
        $params
    );
}

function fetchMaintenanceReportById(mysqli $db, int $maintenanceId): ?array
{
    return dbFetchOne(
        $db,
        "SELECT * FROM maintenance WHERE MaintenanceID = ? AND IsDeleted = 0 LIMIT 1",
        'i',
        [$maintenanceId]
    );
}

function fetchMaintenanceRooms(mysqli $db, bool $onlyActive = true): array
{
    $where = $onlyActive ? 'WHERE IsDeleted = 0' : '';
    return dbFetchAll($db, "SELECT RuanganID, NamaRuangan, Lantai FROM ruangan $where ORDER BY Lantai ASC, NamaRuangan ASC");
}

function fetchMaintenanceInventory(mysqli $db, bool $onlyActive = true): array
{
    $where = $onlyActive ? 'WHERE IsDeleted = 0' : '';
    return dbFetchAll($db, "SELECT InventarisID, NamaBarang FROM inventaris $where ORDER BY NamaBarang ASC");
}

function checkMaintenanceTargetExists(mysqli $db, string $targetType, int $targetId): bool
{
    if ($targetType === 'ruangan') {
        return dbFetchOne($db, "SELECT RuanganID FROM ruangan WHERE RuanganID = ? AND IsDeleted = 0 LIMIT 1", 'i', [$targetId]) !== null;
    }

    return dbFetchOne($db, "SELECT InventarisID FROM inventaris WHERE InventarisID = ? AND IsDeleted = 0 LIMIT 1", 'i', [$targetId]) !== null;
}

function createMaintenanceReport(
    mysqli $db,
    ?int $penghuniId,
    ?int $petugasId,
    ?int $ruanganId,
    ?int $inventarisId,
    string $jenisLaporan,
    string $deskripsi,
    string $fotoLaporan,
    string $tanggalLapor
): bool {
    dbExecute(
        $db,
        "INSERT INTO maintenance (
            PenghuniID, PetugasID, RuanganID, InventarisID,
            JenisLaporan, Deskripsi, FotoLaporan, TanggalLapor, StatusMaintenance
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Diajukan')",
        'iiiissss',
        [$penghuniId, $petugasId, $ruanganId, $inventarisId, $jenisLaporan, $deskripsi, $fotoLaporan, $tanggalLapor]
    );

    return true;
}

function updateMaintenanceReport(
    mysqli $db,
    int $maintenanceId,
    ?int $ruanganId,
    ?int $inventarisId,
    string $jenisLaporan,
    string $deskripsi,
    string $fotoLaporan
): bool {
    dbExecute(
        $db,
        "UPDATE maintenance
         SET RuanganID = ?, InventarisID = ?, JenisLaporan = ?, Deskripsi = ?, FotoLaporan = ?
         WHERE MaintenanceID = ?",
        'iisssi',
        [$ruanganId, $inventarisId, $jenisLaporan, $deskripsi, $fotoLaporan, $maintenanceId]
    );

    return true;
}

function claimMaintenanceReport(mysqli $db, int $maintenanceId, int $petugasId): int
{
    return dbExecute(
        $db,
        "UPDATE maintenance
         SET StatusMaintenance = 'Diproses', PetugasID = ?
         WHERE MaintenanceID = ? AND IsDeleted = 0 AND StatusMaintenance = 'Diajukan'",
        'ii',
        [$petugasId, $maintenanceId]
    );
}

function checkMaintenanceTechnicianOwnership(mysqli $db, int $maintenanceId, int $petugasId): bool
{
    return dbFetchOne(
        $db,
        "SELECT MaintenanceID
         FROM maintenance
         WHERE MaintenanceID = ? AND PetugasID = ? AND StatusMaintenance = 'Diproses' AND IsDeleted = 0
         LIMIT 1",
        'ii',
        [$maintenanceId, $petugasId]
    ) !== null;
}

function completeMaintenanceReport(mysqli $db, int $maintenanceId, string $tanggalSelesai, string $keterangan, string $fotoMaintenance): bool
{
    dbExecute(
        $db,
        "UPDATE maintenance
         SET StatusMaintenance = 'Selesai', TanggalSelesai = ?, Keterangan = ?, FotoMaintenance = ?
         WHERE MaintenanceID = ? AND IsDeleted = 0",
        'sssi',
        [$tanggalSelesai, $keterangan, $fotoMaintenance, $maintenanceId]
    );

    return true;
}

function deleteMaintenanceReport(mysqli $db, int $maintenanceId): bool
{
    dbExecute($db, "UPDATE maintenance SET IsDeleted = 1 WHERE MaintenanceID = ?", 'i', [$maintenanceId]);
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
