<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchMaintenanceReportsForRole(mysqli $db, string $role, int $userId): array
{
    return dbFetchAll($db, "SELECT m.MaintenanceID, m.PenghuniID, m.PetugasID, m.TeknisiID, m.RuanganID, m.InventarisID, m.KamarID, m.TanggalLapor, m.JenisLaporan, m.Deskripsi, m.StatusMaintenance, m.TanggalSelesai, m.Keterangan, (m.FotoLaporan IS NOT NULL AND m.FotoLaporan != '') AS HasFotoLaporan, (m.FotoMaintenance IS NOT NULL AND m.FotoMaintenance != '') AS HasFotoMaintenance, p.NamaPenghuni, p.Nim, pt.NamaPetugas AS NamaReporterPetugas, tech.NamaPetugas AS NamaTeknisi, r.NamaRuangan, r.Lantai AS LantaiRuangan, i.NamaBarang, inv_r.NamaRuangan AS InvRuanganNama, COALESCE(inv_k.NomorKamar, mk.NomorKamar) AS InvKamarNomor FROM maintenance m LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID LEFT JOIN petugas tech ON m.TeknisiID = tech.PetugasID LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID LEFT JOIN ruangan inv_r ON i.RuanganID = inv_r.RuanganID LEFT JOIN kamar inv_k ON i.KamarID = inv_k.KamarID LEFT JOIN kamar mk ON m.KamarID = mk.KamarID WHERE m.IsDeleted = 0 AND (? = 'MAINTENANCE' OR (? = 'PENGHUNI' AND m.PenghuniID = ?) OR (? NOT IN ('MAINTENANCE', 'PENGHUNI') AND m.PetugasID = ? AND m.PenghuniID IS NULL)) ORDER BY CASE WHEN m.JenisLaporan = 'Kerusakan Darurat / Berat' THEN 1 WHEN m.JenisLaporan = 'Kerusakan Sedang' THEN 2 ELSE 3 END, m.MaintenanceID DESC", 'ssisi', [$role, $role, $userId, $role, $userId]);
}

function fetchMaintenanceReportById(mysqli $db, int $maintenanceId): ?array
{
    return dbFetchOne($db, 'SELECT * FROM maintenance WHERE MaintenanceID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$maintenanceId]);
}

function fetchMaintenanceRooms(mysqli $db, bool $onlyActive = true): array
{
    return dbFetchAll($db, 'SELECT RuanganID, NamaRuangan, Lantai FROM ruangan WHERE ? = 0 OR IsDeleted = 0 ORDER BY Lantai ASC, NamaRuangan ASC', 'i', [$onlyActive ? 1 : 0]);
}

function fetchMaintenanceInventory(mysqli $db, bool $onlyActive = true): array
{
    return dbFetchAll($db, 'SELECT InventarisID, NamaBarang, RuanganID, KamarID FROM inventaris WHERE ? = 0 OR IsDeleted = 0 ORDER BY NamaBarang ASC', 'i', [$onlyActive ? 1 : 0]);
}

function checkMaintenanceTargetExists(mysqli $db, string $targetType, int $targetId): bool
{
    if ($targetType === 'ruangan') {
        return dbFetchOne($db, 'SELECT RuanganID FROM ruangan WHERE RuanganID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$targetId]) !== null;
    }
    if ($targetType === 'kamar') {
        return dbFetchOne($db, 'SELECT KamarID FROM kamar WHERE KamarID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$targetId]) !== null;
    }

    return dbFetchOne($db, 'SELECT InventarisID FROM inventaris WHERE InventarisID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$targetId]) !== null;
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
        'UPDATE maintenance SET JenisLaporan = ? WHERE MaintenanceID = ? AND IsDeleted = 0',
        'si',
        [$jenisLaporan, $maintenanceId]
    );

    return true;
}

function claimMaintenanceReport(mysqli $db, int $maintenanceId, int $petugasId): int
{
    return dbExecute($db, "CALL sp_claimMaintenanceReport(?, ?)", 'ii', [$petugasId, $maintenanceId]);
}

function checkMaintenanceTechnicianOwnership(mysqli $db, int $maintenanceId, int $petugasId): bool
{
    return dbFetchOne($db, "SELECT MaintenanceID FROM maintenance WHERE MaintenanceID = ? AND TeknisiID = ? AND StatusMaintenance = 'Diproses' AND IsDeleted = 0", 'ii', [$maintenanceId, $petugasId]) !== null;
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
    dbExecute($db, 'UPDATE maintenance SET IsDeleted = 1 WHERE MaintenanceID = ?', 'i', [$maintenanceId]);
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
    $column = $photoType === 'maintenance_perbaikan' ? 'FotoMaintenance' : 'FotoLaporan';
    $row = dbFetchOne($db, "SELECT $column FROM maintenance WHERE MaintenanceID = ?", 'i', [$maintenanceId]);
    if ($row === null) {
        return null;
    }

    return reset($row) ?: null;
}
