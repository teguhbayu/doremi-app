-- Doremi App Database Migration
-- Adds RuanganID to sp_getMaintenanceInventory so the maintenance report form
-- can filter the inventory dropdown to items belonging to the selected ruangan.

DROP PROCEDURE IF EXISTS sp_getMaintenanceInventory;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceInventory(IN only_active_param TINYINT)
BEGIN
    SELECT InventarisID, NamaBarang, RuanganID
    FROM inventaris
    WHERE only_active_param = 0 OR IsDeleted = 0
    ORDER BY NamaBarang ASC;
END;
-- QUERY_SEPARATOR
