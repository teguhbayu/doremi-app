-- Doremi App Database Migration
-- Adds sp_getInventarisByKamar so the kamar detail page can list the active
-- inventory items assigned to a specific kamar.

DROP PROCEDURE IF EXISTS sp_getInventarisByKamar;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInventarisByKamar(IN kamar_id_param INT)
BEGIN
    SELECT InventarisID, NamaBarang, Jumlah, Keterangan, UpdatedAt
    FROM inventaris
    WHERE KamarID = kamar_id_param AND IsDeleted = 0
    ORDER BY NamaBarang ASC;
END;
-- QUERY_SEPARATOR
