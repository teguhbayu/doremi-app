-- Doremi App Database Migration
-- Adds a 'custom' date-range mode to every report stored procedure (paket, inout,
-- maintenance). Widens range_param to VARCHAR(10) and adds two new trailing
-- IN parameters (start_date_param DATE, end_date_param DATE) used only when
-- range_param = 'custom'. Existing preset behavior (7d/30d/6m/all) is unchanged.

-- -----------------------------------------------------
-- PAKET REPORT PROCEDURES
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getPaketReportExport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportExport(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pk.NamaPengirim, pk.Kurir, pk.JenisPaket, pk.WaktuSampai,
           ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
           COALESCE(pp.Status, 'Belum Diambil') AS Status, pp.WaktuPengambilan,
           CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL AND pk.WaktuSampai IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) ELSE NULL END AS Durasi
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    LEFT JOIN kamar k ON ph.KamarID = k.KamarID
    LEFT JOIN petugas pt ON pk.PetugasID = pt.PetugasID
    LEFT JOIN (
        SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan, pp1.PetugasID AS PickupPetugasID
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    ORDER BY pk.WaktuSampai DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportStats(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Sudah Diambil' THEN 1 ELSE 0 END) AS sudah,
        SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Belum Diambil' THEN 1 ELSE 0 END) AS belum,
        SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'TERTUKAR' THEN 1 ELSE 0 END) AS tertukar,
        ROUND(AVG(CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL AND pk.WaktuSampai IS NOT NULL
            THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) ELSE NULL END)) AS avg_menit
    FROM paket pk
    LEFT JOIN (
        SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan, pp1.PetugasID AS PickupPetugasID
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all';
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportStatusDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportStatusDist(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT COALESCE(pp.Status, 'Belum Diambil') AS s, COUNT(*) AS n
    FROM paket pk
    LEFT JOIN (
        SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan, pp1.PetugasID AS PickupPetugasID
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY s;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTrendDaily(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT DATE(pk.WaktuSampai) AS d, COUNT(*) AS n
    FROM paket pk
    WHERE pk.WaktuSampai IS NOT NULL
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
      )
    GROUP BY d
    ORDER BY d ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTrendMonthly(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT DATE_FORMAT(pk.WaktuSampai, '%Y-%m') AS m, COUNT(*) AS n
    FROM paket pk
    WHERE pk.WaktuSampai IS NOT NULL
      AND (
          (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY m
    ORDER BY m ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTipeDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTipeDist(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pk.JenisPaket AS j, COUNT(*) AS n
    FROM paket pk
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY j;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTopKurir;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTopKurir(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pk.Kurir, COUNT(*) AS n
    FROM paket pk
    WHERE pk.Kurir <> ''
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY pk.Kurir
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportJamSibuk;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportJamSibuk(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT HOUR(pk.WaktuSampai) AS h, COUNT(*) AS n
    FROM paket pk
    WHERE pk.WaktuSampai IS NOT NULL
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY h;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTopPenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTopPenghuni(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT ph.NamaPenghuni, COUNT(*) AS n
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY pk.PenghuniID, ph.NamaPenghuni
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportPetugasRanking;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportPetugasRanking(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pt.NamaPetugas,
           COUNT(*) AS total,
           SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Sudah Diambil' THEN 1 ELSE 0 END) AS sudah,
           SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'TERTUKAR' THEN 1 ELSE 0 END) AS tertukar
    FROM paket pk
    JOIN petugas pt ON pk.PetugasID = pt.PetugasID
    LEFT JOIN (
        SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan, pp1.PetugasID AS PickupPetugasID
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE pt.Jabatan = 'SIGAP' AND pt.IsDeleted = 0
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY pk.PetugasID, pt.NamaPetugas
    ORDER BY total DESC, sudah DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportDetail(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pk.PaketID, pk.NamaPengirim, pk.Kurir, pk.JenisPaket, pk.WaktuSampai,
           ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
           COALESCE(pp.Status, 'Belum Diambil') AS Status, pp.WaktuPengambilan,
           CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL AND pk.WaktuSampai IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) ELSE NULL END AS Durasi
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    LEFT JOIN kamar k ON ph.KamarID = k.KamarID
    LEFT JOIN petugas pt ON pk.PetugasID = pt.PetugasID
    LEFT JOIN (
        SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan, pp1.PetugasID AS PickupPetugasID
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND pk.WaktuSampai >= start_date_param AND pk.WaktuSampai < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    ORDER BY pk.WaktuSampai DESC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- INOUT REPORT PROCEDURES
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getInOutReportExport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportExport(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT io.Status, io.Keperluan, io.WaktuKeluar, io.WaktuMasuk,
           p.NamaPenghuni, p.Nim, k.NomorKamar, pt.NamaPetugas,
           CASE WHEN io.Status = 'Masuk'
                THEN TIMESTAMPDIFF(MINUTE, io.WaktuKeluar, io.WaktuMasuk) ELSE NULL END AS Durasi
    FROM inoutpenghuni io
    JOIN penghuni p ON io.PenghuniID = p.PenghuniID
    JOIN kamar k ON p.KamarID = k.KamarID
    LEFT JOIN petugas pt ON io.PetugasID = pt.PetugasID AND io.PetugasID <> 0
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    ORDER BY io.WaktuKeluar DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportStats(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN io.Status = 'Masuk'   THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN io.Status = 'Keluar'  THEN 1 ELSE 0 END) AS diluar,
        SUM(CASE WHEN io.Status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        ROUND(AVG(CASE WHEN io.Status = 'Masuk'
            THEN TIMESTAMPDIFF(MINUTE, io.WaktuKeluar, io.WaktuMasuk) ELSE NULL END)) AS avg_menit
    FROM inoutpenghuni io
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all';
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportStatusDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportStatusDist(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT io.Status, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY io.Status;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTrendDaily(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT DATE(io.WaktuKeluar) AS d, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
    GROUP BY d
    ORDER BY d ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTrendMonthly(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT DATE_FORMAT(io.WaktuKeluar, '%Y-%m') AS m, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY m
    ORDER BY m ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportPeakHour;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportPeakHour(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT HOUR(io.WaktuKeluar) AS h, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE io.Status IN ('Keluar', 'Masuk')
      AND (
          (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY h;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTopPenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTopPenghuni(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT p.NamaPenghuni, COUNT(*) AS n
    FROM inoutpenghuni io
    JOIN penghuni p ON io.PenghuniID = p.PenghuniID
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY io.PenghuniID, p.NamaPenghuni
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportGenderDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportGenderDist(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT p.JenisKelamin AS g, COUNT(*) AS n
    FROM inoutpenghuni io
    JOIN penghuni p ON io.PenghuniID = p.PenghuniID
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    GROUP BY g;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTopKeperluan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTopKeperluan(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT io.Keperluan, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE io.Keperluan <> ''
      AND (
          (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY io.Keperluan
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportPetugasRanking;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportPetugasRanking(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pt.NamaPetugas,
           COUNT(*) AS total,
           SUM(CASE WHEN io.Status = 'Masuk'  THEN 1 ELSE 0 END) AS selesai,
           SUM(CASE WHEN io.Status = 'Keluar' THEN 1 ELSE 0 END) AS diluar
    FROM inoutpenghuni io
    JOIN petugas pt ON io.PetugasID = pt.PetugasID
    WHERE io.PetugasID <> 0 AND pt.Jabatan = 'SIGAP' AND pt.IsDeleted = 0
      AND (
          (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY io.PetugasID, pt.NamaPetugas
    ORDER BY total DESC, selesai DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportDetail(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT io.InOutID, io.Status, io.Keperluan, io.WaktuKeluar, io.WaktuMasuk,
           p.NamaPenghuni, p.Nim, k.NomorKamar, pt.NamaPetugas,
           CASE WHEN io.Status = 'Masuk'
                THEN TIMESTAMPDIFF(MINUTE, io.WaktuKeluar, io.WaktuMasuk) ELSE NULL END AS Durasi
    FROM inoutpenghuni io
    JOIN penghuni p ON io.PenghuniID = p.PenghuniID
    JOIN kamar k ON p.KamarID = k.KamarID
    LEFT JOIN petugas pt ON io.PetugasID = pt.PetugasID AND io.PetugasID <> 0
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR (range_param = 'custom' AND io.WaktuKeluar >= start_date_param AND io.WaktuKeluar < DATE_ADD(end_date_param, INTERVAL 1 DAY))
       OR range_param = 'all'
    ORDER BY io.WaktuKeluar DESC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- MAINTENANCE REPORT PROCEDURES
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportExport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportExport(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT m.JenisLaporan, m.Deskripsi, m.StatusMaintenance, m.TanggalLapor, m.TanggalSelesai,
           COALESCE(p.NamaPenghuni, pt.NamaPetugas, 'Staff') AS Pelapor,
           COALESCE(r.NamaRuangan, i.NamaBarang, '-') AS Lokasi,
           tech.NamaPetugas AS Petugas,
           CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
                THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END AS Durasi
    FROM maintenance m
    LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
    LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
    LEFT JOIN petugas tech ON m.TeknisiID = tech.PetugasID
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    ORDER BY m.TanggalLapor DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportStats(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN m.StatusMaintenance = 'Selesai'  THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN m.StatusMaintenance = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
        SUM(CASE WHEN m.StatusMaintenance = 'Diajukan' THEN 1 ELSE 0 END) AS diajukan,
        ROUND(AVG(CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
            THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END), 1) AS avg_hari
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      );
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportPriorityDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportPriorityDist(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT m.JenisLaporan, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY m.JenisLaporan;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportTrendDaily(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT DATE(m.TanggalLapor) AS d, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
      )
    GROUP BY d
    ORDER BY d ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportTrendMonthly(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT DATE_FORMAT(m.TanggalLapor, '%Y-%m') AS bulan, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY bulan
    ORDER BY bulan ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportTopRuangan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportTopRuangan(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT r.NamaRuangan, COUNT(*) AS n
    FROM maintenance m
    JOIN ruangan r ON m.RuanganID = r.RuanganID
    WHERE m.RuanganID IS NOT NULL AND m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY m.RuanganID, r.NamaRuangan
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportStackedStatus;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportStackedStatus(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT m.JenisLaporan, m.StatusMaintenance, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY m.JenisLaporan, m.StatusMaintenance;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportPetugasRanking;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportPetugasRanking(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT pt.NamaPetugas,
           COUNT(m.MaintenanceID) AS total,
           SUM(CASE WHEN m.StatusMaintenance = 'Selesai' THEN 1 ELSE 0 END) AS selesai,
           SUM(CASE WHEN m.StatusMaintenance = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
           ROUND(AVG(CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
               THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END), 1) AS avg_hari
    FROM maintenance m
    JOIN petugas pt ON m.TeknisiID = pt.PetugasID
    WHERE m.TeknisiID IS NOT NULL AND pt.Jabatan = 'MAINTENANCE' AND pt.IsDeleted = 0 AND m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    GROUP BY m.TeknisiID, pt.NamaPetugas
    ORDER BY selesai DESC, total DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportDetail(IN range_param VARCHAR(10), IN start_date_param DATE, IN end_date_param DATE)
BEGIN
    SELECT m.MaintenanceID, m.JenisLaporan, m.StatusMaintenance, m.TanggalLapor, m.TanggalSelesai, m.Deskripsi,
           COALESCE(p.NamaPenghuni, rpt.NamaPetugas, 'Staff') AS Pelapor,
           COALESCE(r.NamaRuangan, i.NamaBarang, '-') AS Lokasi,
           tech.NamaPetugas AS Petugas,
           CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
                THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END AS Durasi
    FROM maintenance m
    LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
    LEFT JOIN petugas rpt ON m.PetugasID = rpt.PetugasID
    LEFT JOIN petugas tech ON m.TeknisiID = tech.PetugasID
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR (range_param = 'custom' AND m.TanggalLapor >= start_date_param AND m.TanggalLapor < DATE_ADD(end_date_param, INTERVAL 1 DAY))
          OR range_param = 'all'
      )
    ORDER BY m.TanggalLapor DESC;
END;
-- QUERY_SEPARATOR
