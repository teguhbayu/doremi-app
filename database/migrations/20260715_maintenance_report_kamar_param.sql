-- Doremi App Database Migration
-- Adds KamarID support to the maintenance create/update stored procedures so a
-- report can record which kamar an inventory item belongs to (in addition to
-- RuanganID / InventarisID). Brings the repo in sync with the live database,
-- whose procedures already carry the extra kamar_id parameter.

DROP PROCEDURE IF EXISTS sp_createMaintenanceReport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createMaintenanceReport(
    IN penghuni_id_param INT,
    IN petugas_id_param INT,
    IN ruangan_id_param INT,
    IN inventaris_id_param INT,
    IN kamar_id_param INT,
    IN jenis_laporan_param VARCHAR(50),
    IN deskripsi_param TEXT,
    IN foto_laporan_param LONGTEXT,
    IN tanggal_lapor_param DATETIME
)
BEGIN
    INSERT INTO maintenance (
        PenghuniID, PetugasID, RuanganID, InventarisID, KamarID,
        JenisLaporan, Deskripsi, FotoLaporan, TanggalLapor, StatusMaintenance
    ) VALUES (
        penghuni_id_param, petugas_id_param, ruangan_id_param, inventaris_id_param, kamar_id_param,
        jenis_laporan_param, deskripsi_param, foto_laporan_param, tanggal_lapor_param, 'Diajukan'
    );
END;
-- QUERY_SEPARATOR
DROP PROCEDURE IF EXISTS sp_updateMaintenanceReport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updateMaintenanceReport(
    IN id_param INT,
    IN ruangan_id_param INT,
    IN inventaris_id_param INT,
    IN kamar_id_param INT,
    IN jenis_laporan_param VARCHAR(50),
    IN deskripsi_param TEXT,
    IN foto_laporan_param LONGTEXT
)
BEGIN
    UPDATE maintenance
    SET RuanganID = ruangan_id_param,
        InventarisID = inventaris_id_param,
        KamarID = kamar_id_param,
        JenisLaporan = jenis_laporan_param,
        Deskripsi = deskripsi_param,
        FotoLaporan = foto_laporan_param
    WHERE MaintenanceID = id_param;
END;
-- QUERY_SEPARATOR
