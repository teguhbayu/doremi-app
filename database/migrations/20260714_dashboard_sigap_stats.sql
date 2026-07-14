DROP PROCEDURE IF EXISTS sp_getDashboardSigapStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardSigapStats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM inoutpenghuni WHERE Status = 'Pending') AS pendingConfirmation,
        (SELECT COUNT(*) FROM inoutpenghuni WHERE Status = 'Keluar') AS currentlyOutside,
        (
            SELECT COUNT(*)
            FROM paket pk
            LEFT JOIN (
                SELECT pp1.*
                FROM pengambilanpaket pp1
                INNER JOIN (
                    SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID
                    FROM pengambilanpaket
                    GROUP BY PaketID
                ) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID
            ) pp ON pp.PaketID = pk.PaketID
            WHERE pp.PengambilanPaketID IS NULL OR pp.Status = 'Belum Diambil'
        ) AS pendingPackagePickup,
        (SELECT COUNT(*) FROM paket WHERE DATE(WaktuSampai) = CURDATE()) AS packagesToday;
END;
-- QUERY_SEPARATOR
