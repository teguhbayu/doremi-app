<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function inOutReportRangeSql(): string { return "((? = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)) OR (? = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)) OR (? = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)) OR (? = 'custom' AND io.WaktuKeluar >= ? AND io.WaktuKeluar < DATE_ADD(?, INTERVAL 1 DAY)) OR ? = 'all')"; }
function inOutReportRangeParams(string $range, ?string $startDate, ?string $endDate): array { return [$range, $range, $range, $range, $startDate, $endDate, $range]; }

function fetchInOutReportExport(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT io.InOutID, io.Status, io.Keperluan, io.WaktuKeluar, io.WaktuMasuk, p.NamaPenghuni, p.Nim, k.NomorKamar, pt.NamaPetugas, CASE WHEN io.Status = 'Masuk' THEN TIMESTAMPDIFF(MINUTE, io.WaktuKeluar, io.WaktuMasuk) END AS Durasi FROM inoutpenghuni io JOIN penghuni p ON io.PenghuniID=p.PenghuniID JOIN kamar k ON p.KamarID=k.KamarID LEFT JOIN petugas pt ON io.PetugasID=pt.PetugasID AND io.PetugasID<>0 WHERE " . inOutReportRangeSql() . ' ORDER BY io.WaktuKeluar DESC', 'sssssss', inOutReportRangeParams($range,$startDate,$endDate));
}

function fetchInOutReportStats(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchOne($db, "SELECT COUNT(*) AS total, SUM(io.Status='Masuk') AS selesai, SUM(io.Status='Keluar') AS diluar, SUM(io.Status='Pending') AS pending, ROUND(AVG(CASE WHEN io.Status='Masuk' THEN TIMESTAMPDIFF(MINUTE,io.WaktuKeluar,io.WaktuMasuk) END)) AS avg_menit FROM inoutpenghuni io WHERE " . inOutReportRangeSql(), 'sssssss', inOutReportRangeParams($range,$startDate,$endDate)) ?? [];
}

function fetchInOutReportStatusDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT io.Status, COUNT(*) AS n FROM inoutpenghuni io WHERE ' . inOutReportRangeSql() . ' GROUP BY io.Status', 'sssssss', inOutReportRangeParams($range,$startDate,$endDate));
}

function fetchInOutReportTrendDaily(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT DATE(io.WaktuKeluar) AS d, COUNT(*) AS n FROM inoutpenghuni io WHERE ' . inOutReportRangeSql() . ' GROUP BY d ORDER BY d ASC', 'sssssss', inOutReportRangeParams($range,$startDate,$endDate));
}

function fetchInOutReportTrendMonthly(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT DATE_FORMAT(io.WaktuKeluar, '%Y-%m') AS m, COUNT(*) AS n FROM inoutpenghuni io WHERE " . inOutReportRangeSql() . ' GROUP BY m ORDER BY m ASC', 'sssssss', inOutReportRangeParams($range,$startDate,$endDate));
}

function fetchInOutReportPeakHour(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT HOUR(io.WaktuKeluar) AS h, COUNT(*) AS n FROM inoutpenghuni io WHERE io.Status IN ('Keluar','Masuk') AND " . inOutReportRangeSql() . ' GROUP BY h', 'sssssss', inOutReportRangeParams($range,$startDate,$endDate));
}

function fetchInOutReportTopPenghuni(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT p.NamaPenghuni, COUNT(*) AS n FROM inoutpenghuni io JOIN penghuni p ON io.PenghuniID = p.PenghuniID WHERE ' . inOutReportRangeSql() . ' GROUP BY io.PenghuniID, p.NamaPenghuni ORDER BY n DESC LIMIT 5', 'sssssss', inOutReportRangeParams($range, $startDate, $endDate));
}

function fetchInOutReportGenderDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT p.JenisKelamin AS g, COUNT(*) AS n FROM inoutpenghuni io JOIN penghuni p ON io.PenghuniID = p.PenghuniID WHERE ' . inOutReportRangeSql() . ' GROUP BY g', 'sssssss', inOutReportRangeParams($range, $startDate, $endDate));
}

function fetchInOutReportTopKeperluan(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT io.Keperluan, COUNT(*) AS n FROM inoutpenghuni io WHERE io.Keperluan <> '' AND " . inOutReportRangeSql() . ' GROUP BY io.Keperluan ORDER BY n DESC LIMIT 5', 'sssssss', inOutReportRangeParams($range, $startDate, $endDate));
}

function fetchInOutReportPetugasRanking(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT pt.NamaPetugas, COUNT(*) AS total, SUM(io.Status='Masuk') AS selesai, SUM(io.Status='Keluar') AS diluar FROM inoutpenghuni io JOIN petugas pt ON io.PetugasID = pt.PetugasID WHERE io.PetugasID <> 0 AND pt.Jabatan = 'SIGAP' AND pt.IsDeleted = 0 AND " . inOutReportRangeSql() . ' GROUP BY io.PetugasID, pt.NamaPetugas ORDER BY total DESC, selesai DESC', 'sssssss', inOutReportRangeParams($range, $startDate, $endDate));
}

function fetchInOutReportDetail(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return fetchInOutReportExport($db, $range, $startDate, $endDate);
}
