<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function maintenanceReportRangeSql(): string { return "((?='7d' AND m.TanggalLapor>=DATE_SUB(CURDATE(),INTERVAL 6 DAY)) OR (?='30d' AND m.TanggalLapor>=DATE_SUB(CURDATE(),INTERVAL 29 DAY)) OR (?='6m' AND m.TanggalLapor>=DATE_SUB(CURDATE(),INTERVAL 5 MONTH)) OR (?='custom' AND m.TanggalLapor>=? AND m.TanggalLapor<DATE_ADD(?,INTERVAL 1 DAY)) OR ?='all')"; }
function maintenanceReportRangeParams(string $range, ?string $startDate, ?string $endDate): array { return [$range,$range,$range,$range,$startDate,$endDate,$range]; }

function fetchMaintenanceReportExport(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT m.MaintenanceID,m.JenisLaporan,m.Deskripsi,m.StatusMaintenance,m.TanggalLapor,m.TanggalSelesai,COALESCE(p.NamaPenghuni,pt.NamaPetugas,'Staff') AS Pelapor,COALESCE(r.NamaRuangan,CONCAT('Kamar ',k.NomorKamar),i.NamaBarang,'-') AS Lokasi,tech.NamaPetugas AS Petugas,CASE WHEN m.StatusMaintenance='Selesai' THEN DATEDIFF(m.TanggalSelesai,m.TanggalLapor) END AS Durasi FROM maintenance m LEFT JOIN penghuni p ON m.PenghuniID=p.PenghuniID LEFT JOIN petugas pt ON m.PetugasID=pt.PetugasID LEFT JOIN petugas tech ON m.TeknisiID=tech.PetugasID LEFT JOIN ruangan r ON m.RuanganID=r.RuanganID LEFT JOIN inventaris i ON m.InventarisID=i.InventarisID LEFT JOIN kamar k ON m.KamarID=k.KamarID WHERE m.IsDeleted=0 AND " . maintenanceReportRangeSql() . ' ORDER BY m.TanggalLapor DESC','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportStats(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchOne($db,"SELECT COUNT(*) AS total,SUM(m.StatusMaintenance='Selesai') AS selesai,SUM(m.StatusMaintenance='Diproses') AS diproses,SUM(m.StatusMaintenance='Diajukan') AS diajukan,ROUND(AVG(CASE WHEN m.StatusMaintenance='Selesai' THEN DATEDIFF(m.TanggalSelesai,m.TanggalLapor) END),1) AS avg_hari FROM maintenance m WHERE m.IsDeleted=0 AND ".maintenanceReportRangeSql(),'sssssss',maintenanceReportRangeParams($range,$startDate,$endDate))??[];
}

function fetchMaintenanceReportPriorityDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db,'SELECT m.JenisLaporan,COUNT(*) AS n FROM maintenance m WHERE m.IsDeleted=0 AND '.maintenanceReportRangeSql().' GROUP BY m.JenisLaporan','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportTrendDaily(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db,'SELECT DATE(m.TanggalLapor) AS d,COUNT(*) AS n FROM maintenance m WHERE m.IsDeleted=0 AND '.maintenanceReportRangeSql().' GROUP BY d ORDER BY d ASC','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportTrendMonthly(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db,"SELECT DATE_FORMAT(m.TanggalLapor,'%Y-%m') AS bulan,COUNT(*) AS n FROM maintenance m WHERE m.IsDeleted=0 AND ".maintenanceReportRangeSql().' GROUP BY bulan ORDER BY bulan ASC','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportTopRuangan(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db,'SELECT r.NamaRuangan,COUNT(*) AS n FROM maintenance m JOIN ruangan r ON m.RuanganID=r.RuanganID WHERE m.RuanganID IS NOT NULL AND m.IsDeleted=0 AND '.maintenanceReportRangeSql().' GROUP BY m.RuanganID,r.NamaRuangan ORDER BY n DESC LIMIT 5','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportStackedStatus(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db,'SELECT m.JenisLaporan,m.StatusMaintenance,COUNT(*) AS n FROM maintenance m WHERE m.IsDeleted=0 AND '.maintenanceReportRangeSql().' GROUP BY m.JenisLaporan,m.StatusMaintenance','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportPetugasRanking(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db,"SELECT pt.NamaPetugas,COUNT(m.MaintenanceID) AS total,SUM(m.StatusMaintenance='Selesai') AS selesai,SUM(m.StatusMaintenance='Diproses') AS diproses,ROUND(AVG(CASE WHEN m.StatusMaintenance='Selesai' THEN DATEDIFF(m.TanggalSelesai,m.TanggalLapor) END),1) AS avg_hari FROM maintenance m JOIN petugas pt ON m.TeknisiID=pt.PetugasID WHERE m.TeknisiID IS NOT NULL AND pt.Jabatan='MAINTENANCE' AND pt.IsDeleted=0 AND m.IsDeleted=0 AND ".maintenanceReportRangeSql().' GROUP BY m.TeknisiID,pt.NamaPetugas ORDER BY selesai DESC,total DESC','sssssss',maintenanceReportRangeParams($range,$startDate,$endDate));
}

function fetchMaintenanceReportDetail(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return fetchMaintenanceReportExport($db, $range, $startDate, $endDate);
}
