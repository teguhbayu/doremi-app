-- Doremi App Database Migration
-- Adds sp_updateMaintenanceUrgency stored procedure

DROP PROCEDURE IF EXISTS sp_updateMaintenanceUrgency;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updateMaintenanceUrgency(IN id_param INT, IN jenis_laporan_param VARCHAR(50))
BEGIN
    UPDATE maintenance
    SET JenisLaporan = jenis_laporan_param
    WHERE MaintenanceID = id_param AND IsDeleted = 0;
END;
-- QUERY_SEPARATOR
