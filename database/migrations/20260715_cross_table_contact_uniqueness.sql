-- Doremi App Database Migration
-- Adds stored procedures to check that Email/NoHP are not shared between penghuni and petugas

DROP PROCEDURE IF EXISTS sp_checkPetugasContactExists;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkPetugasContactExists(IN email_param VARCHAR(100), IN no_hp_param VARCHAR(20))
BEGIN
    SELECT PetugasID, Email, NoHP
    FROM petugas
    WHERE IsDeleted = 0 AND (Email = email_param OR NoHP = no_hp_param)
    LIMIT 1;
END;
-- QUERY_SEPARATOR
DROP PROCEDURE IF EXISTS sp_checkPenghuniContactExists;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkPenghuniContactExists(IN email_param VARCHAR(100), IN no_hp_param VARCHAR(20))
BEGIN
    SELECT PenghuniID, Email, NoHP
    FROM penghuni
    WHERE IsDeleted = 0 AND (Email = email_param OR NoHP = no_hp_param)
    LIMIT 1;
END;
-- QUERY_SEPARATOR
