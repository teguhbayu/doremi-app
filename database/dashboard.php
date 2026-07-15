<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchDashboardPengurusStats(mysqli $db): array
{
    return dbFetchOne($db, "SELECT (SELECT COUNT(*) FROM penghuni WHERE IsDeleted = 0 AND IsActive = 1) AS activePenghuni, (SELECT COUNT(*) FROM inoutpenghuni WHERE Status = 'Pending') AS pendingInOut, (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Diajukan' AND IsDeleted = 0) AS pendingMaintenance, (SELECT COUNT(*) FROM paket pk LEFT JOIN (SELECT pp1.* FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID) pp ON pp.PaketID = pk.PaketID WHERE pp.PengambilanPaketID IS NULL OR pp.Status = 'Belum Diambil') AS pendingPackagePickup") ?? [
        'activePenghuni' => 0,
        'pendingInOut' => 0,
        'pendingMaintenance' => 0,
        'pendingPackagePickup' => 0,
    ];
}

function fetchDashboardSigapStats(mysqli $db): array
{
    return dbFetchOne($db, "SELECT (SELECT COUNT(*) FROM inoutpenghuni WHERE Status = 'Pending') AS pendingConfirmation, (SELECT COUNT(*) FROM inoutpenghuni WHERE Status = 'Keluar') AS currentlyOutside, (SELECT COUNT(*) FROM paket pk LEFT JOIN (SELECT pp1.* FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID) pp ON pp.PaketID = pk.PaketID WHERE pp.PengambilanPaketID IS NULL OR pp.Status = 'Belum Diambil') AS pendingPackagePickup, (SELECT COUNT(*) FROM paket WHERE DATE(WaktuSampai) = CURDATE()) AS packagesToday") ?? [
        'pendingConfirmation' => 0,
        'currentlyOutside' => 0,
        'pendingPackagePickup' => 0,
        'packagesToday' => 0,
    ];
}

function fetchDashboardGenderStats(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT JenisKelamin, COUNT(*) AS count FROM penghuni WHERE IsDeleted = 0 GROUP BY JenisKelamin');
}

function fetchDashboardPenghuniIzinAktif(mysqli $db, int $userId): int
{
    return (int) dbFetchValue($db, "SELECT COUNT(*) AS total FROM inoutpenghuni WHERE PenghuniID = ? AND Status IN ('Pending', 'Keluar')", 'i', [$userId]);
}

function fetchDashboardPenghuniPaketSummary(mysqli $db, int $userId): array
{
    return dbFetchAll($db, "SELECT COALESCE(pp.Status, 'Belum Diambil') AS Status FROM paket pk LEFT JOIN (SELECT pp1.* FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID) pp ON pp.PaketID = pk.PaketID WHERE pk.PenghuniID = ?", 'i', [$userId]);
}

function fetchDashboardPenghuniMaintenanceSummary(mysqli $db, int $userId): array
{
    return dbFetchAll($db, 'SELECT StatusMaintenance FROM maintenance WHERE PenghuniID = ? AND IsDeleted = 0', 'i', [$userId]);
}

function fetchDashboardMaintenanceCounts(mysqli $db, int $userId): array
{
    return dbFetchOne($db, "SELECT (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Diajukan' AND IsDeleted = 0) AS pendingTasks, (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Diproses' AND TeknisiID = ? AND IsDeleted = 0) AS myOngoingTasks, (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Selesai' AND TeknisiID = ? AND IsDeleted = 0) AS myCompletedTasks, (SELECT COUNT(*) FROM maintenance WHERE JenisLaporan = 'Kerusakan Darurat / Berat' AND StatusMaintenance != 'Selesai' AND IsDeleted = 0) AS activeEmergencyTasks", 'ii', [$userId, $userId]) ?? [
        'pendingTasks' => 0,
        'myOngoingTasks' => 0,
        'myCompletedTasks' => 0,
        'activeEmergencyTasks' => 0,
    ];
}

function fetchDashboardMyTasks(mysqli $db, int $userId): array
{
    return dbFetchAll($db, "SELECT m.MaintenanceID, m.JenisLaporan, m.Deskripsi, m.TanggalLapor, r.NamaRuangan, r.Lantai AS LantaiRuangan, p.NamaPenghuni, pt.NamaPetugas AS NamaReporterPetugas, i.NamaBarang FROM maintenance m LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID WHERE m.StatusMaintenance = 'Diproses' AND m.TeknisiID = ? AND m.IsDeleted = 0 ORDER BY m.MaintenanceID DESC LIMIT 5", 'i', [$userId]);
}

function fetchDashboardEmergencyList(mysqli $db): array
{
    return dbFetchAll($db, "SELECT m.MaintenanceID, m.Deskripsi, m.StatusMaintenance, m.TanggalLapor, r.NamaRuangan, r.Lantai AS LantaiRuangan, p.NamaPenghuni, pt.NamaPetugas AS NamaReporterPetugas, i.NamaBarang FROM maintenance m LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID WHERE m.JenisLaporan = 'Kerusakan Darurat / Berat' AND m.StatusMaintenance != 'Selesai' AND m.IsDeleted = 0 ORDER BY m.TanggalLapor ASC");
}

function fetchMaintenanceStatusPie(mysqli $db): array
{
    return dbFetchAll($db, "SELECT StatusMaintenance, COUNT(*) AS total FROM maintenance WHERE StatusMaintenance IN ('Diajukan', 'Diproses', 'Selesai') AND IsDeleted = 0 GROUP BY StatusMaintenance");
}

function fetchMaintenanceTrendDaily(mysqli $db, int $intervalDays): array
{
    return dbFetchAll($db, 'SELECT DATE(TanggalLapor) AS hari, COUNT(*) AS total FROM maintenance WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND IsDeleted = 0 GROUP BY hari ORDER BY hari ASC', 'i', [$intervalDays]);
}

function fetchMaintenanceTrendMonthly(mysqli $db): array
{
    return dbFetchAll($db, "SELECT DATE_FORMAT(TanggalLapor, '%Y-%m') AS bulan, COUNT(*) AS total FROM maintenance WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) AND IsDeleted = 0 GROUP BY bulan ORDER BY bulan ASC");
}
