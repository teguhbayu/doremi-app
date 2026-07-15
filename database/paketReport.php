<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function paketReportRangeSql(string $column = 'pk.WaktuSampai'): string
{
    return "((? = '7d' AND $column >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)) OR (? = '30d' AND $column >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)) OR (? = '6m' AND $column >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)) OR (? = 'custom' AND $column >= ? AND $column < DATE_ADD(?, INTERVAL 1 DAY)) OR ? = 'all')";
}

function paketReportRangeParams(string $range, ?string $startDate, ?string $endDate): array
{
    return [$range, $range, $range, $range, $startDate, $endDate, $range];
}

function paketReportLatestPickupSql(): string
{
    return "(SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestID = pp1.PengambilanPaketID) pp";
}

function fetchPaketReportExport(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "SELECT pk.NamaPengirim, pk.Kurir, pk.JenisPaket, pk.WaktuSampai, ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas, COALESCE(pp.Status, 'Belum Diambil') AS Status, pp.WaktuPengambilan, CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) END AS Durasi FROM paket pk JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID LEFT JOIN kamar k ON ph.KamarID = k.KamarID LEFT JOIN petugas pt ON pk.PetugasID = pt.PetugasID LEFT JOIN (SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestID = pp1.PengambilanPaketID) pp ON pp.PaketID = pk.PaketID WHERE " . paketReportRangeSql() . ' ORDER BY pk.WaktuSampai DESC';
    return dbFetchAll($db, $sql, 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportStats(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "SELECT COUNT(*) AS total, SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Sudah Diambil' THEN 1 ELSE 0 END) AS sudah, SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Belum Diambil' THEN 1 ELSE 0 END) AS belum, SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'TERTUKAR' THEN 1 ELSE 0 END) AS tertukar, ROUND(AVG(CASE WHEN pp.Status = 'Sudah Diambil' THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) END)) AS avg_menit FROM paket pk LEFT JOIN " . paketReportLatestPickupSql() . ' ON pp.PaketID = pk.PaketID WHERE ' . paketReportRangeSql();
    return dbFetchOne($db, $sql, 'sssssss', paketReportRangeParams($range, $startDate, $endDate)) ?? [];
}

function fetchPaketReportStatusDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "SELECT COALESCE(pp.Status, 'Belum Diambil') AS s, COUNT(*) AS n FROM paket pk LEFT JOIN " . paketReportLatestPickupSql() . ' ON pp.PaketID = pk.PaketID WHERE ' . paketReportRangeSql() . ' GROUP BY s';
    return dbFetchAll($db, $sql, 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportTrendDaily(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT DATE(pk.WaktuSampai) AS d, COUNT(*) AS n FROM paket pk WHERE pk.WaktuSampai IS NOT NULL AND ' . paketReportRangeSql() . ' GROUP BY d ORDER BY d ASC', 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportTrendMonthly(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT DATE_FORMAT(pk.WaktuSampai, '%Y-%m') AS m, COUNT(*) AS n FROM paket pk WHERE pk.WaktuSampai IS NOT NULL AND " . paketReportRangeSql() . ' GROUP BY m ORDER BY m ASC', 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportTipeDist(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT pk.JenisPaket AS j, COUNT(*) AS n FROM paket pk WHERE ' . paketReportRangeSql() . ' GROUP BY j', 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportTopKurir(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, "SELECT pk.Kurir, COUNT(*) AS n FROM paket pk WHERE pk.Kurir <> '' AND " . paketReportRangeSql() . ' GROUP BY pk.Kurir ORDER BY n DESC LIMIT 5', 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportJamSibuk(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT HOUR(pk.WaktuSampai) AS h, COUNT(*) AS n FROM paket pk WHERE pk.WaktuSampai IS NOT NULL AND ' . paketReportRangeSql() . ' GROUP BY h', 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportTopPenghuni(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return dbFetchAll($db, 'SELECT ph.NamaPenghuni, COUNT(*) AS n FROM paket pk JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID WHERE ' . paketReportRangeSql() . ' GROUP BY pk.PenghuniID, ph.NamaPenghuni ORDER BY n DESC LIMIT 5', 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportPetugasRanking(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "SELECT pt.NamaPetugas, COUNT(*) AS total, SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Sudah Diambil' THEN 1 ELSE 0 END) AS sudah, SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'TERTUKAR' THEN 1 ELSE 0 END) AS tertukar FROM paket pk JOIN petugas pt ON pk.PetugasID = pt.PetugasID LEFT JOIN " . paketReportLatestPickupSql() . " ON pp.PaketID = pk.PaketID WHERE pt.Jabatan = 'SIGAP' AND pt.IsDeleted = 0 AND " . paketReportRangeSql() . ' GROUP BY pk.PetugasID, pt.NamaPetugas ORDER BY total DESC, sudah DESC';
    return dbFetchAll($db, $sql, 'sssssss', paketReportRangeParams($range, $startDate, $endDate));
}

function fetchPaketReportDetail(mysqli $db, string $range, ?string $startDate = null, ?string $endDate = null): array
{
    return fetchPaketReportExport($db, $range, $startDate, $endDate);
}
