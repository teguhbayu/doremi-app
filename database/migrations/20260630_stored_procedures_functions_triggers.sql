-- Doremi App Database Migrations
-- Stored Procedures, User-Defined Functions, and Triggers

-- -----------------------------------------------------
-- 1. USER DEFINED FUNCTIONS (UDF)
-- -----------------------------------------------------

DROP FUNCTION IF EXISTS udf_normalizeNim;
-- QUERY_SEPARATOR
CREATE FUNCTION udf_normalizeNim(nim VARCHAR(100))
RETURNS VARCHAR(100)
DETERMINISTIC
BEGIN
    RETURN UPPER(REPLACE(TRIM(nim), ' ', ''));
END;
-- QUERY_SEPARATOR

DROP FUNCTION IF EXISTS udf_normalizeEmail;
-- QUERY_SEPARATOR
CREATE FUNCTION udf_normalizeEmail(email VARCHAR(100))
RETURNS VARCHAR(100)
DETERMINISTIC
BEGIN
    RETURN LOWER(TRIM(email));
END;
-- QUERY_SEPARATOR

DROP FUNCTION IF EXISTS udf_normalizePhone;
-- QUERY_SEPARATOR
CREATE FUNCTION udf_normalizePhone(phone VARCHAR(100))
RETURNS VARCHAR(100)
DETERMINISTIC
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE temp VARCHAR(100) DEFAULT '';
    DECLARE c CHAR(1);
    IF phone IS NULL THEN
        RETURN NULL;
    END IF;
    WHILE i <= CHAR_LENGTH(phone) DO
        SET c = SUBSTRING(phone, i, 1);
        IF c REGEXP '[0-9]' THEN
            SET temp = CONCAT(temp, c);
        END IF;
        SET i = i + 1;
    END WHILE;
    RETURN temp;
END;
-- QUERY_SEPARATOR

DROP FUNCTION IF EXISTS udf_formatPenghuniLabel;
-- QUERY_SEPARATOR
CREATE FUNCTION udf_formatPenghuniLabel(nama VARCHAR(100), nim VARCHAR(10), nomorKamar VARCHAR(20))
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE label VARCHAR(255);
    SET label = TRIM(COALESCE(nama, ''));
    IF nim IS NOT NULL AND TRIM(nim) != '' THEN
        SET label = CONCAT(label, ' (', TRIM(nim), ')');
    END IF;
    IF nomorKamar IS NOT NULL AND TRIM(nomorKamar) != '' THEN
        SET label = CONCAT(label, ' - Kamar ', TRIM(nomorKamar));
    END IF;
    RETURN label;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 2. TRIGGERS (Auto-normalization and timestamps)
-- -----------------------------------------------------

DROP TRIGGER IF EXISTS trg_beforeInsertPenghuni;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeInsertPenghuni
BEFORE INSERT ON penghuni
FOR EACH ROW
BEGIN
    SET NEW.Nim = udf_normalizeNim(NEW.Nim);
    SET NEW.Email = udf_normalizeEmail(NEW.Email);
    SET NEW.NoHP = udf_normalizePhone(NEW.NoHP);
    SET NEW.UpdateAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeUpdatePenghuni;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeUpdatePenghuni
BEFORE UPDATE ON penghuni
FOR EACH ROW
BEGIN
    SET NEW.Nim = udf_normalizeNim(NEW.Nim);
    SET NEW.Email = udf_normalizeEmail(NEW.Email);
    SET NEW.NoHP = udf_normalizePhone(NEW.NoHP);
    SET NEW.UpdateAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeInsertPetugas;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeInsertPetugas
BEFORE INSERT ON petugas
FOR EACH ROW
BEGIN
    SET NEW.Email = udf_normalizeEmail(NEW.Email);
    SET NEW.NoHP = udf_normalizePhone(NEW.NoHP);
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeUpdatePetugas;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeUpdatePetugas
BEFORE UPDATE ON petugas
FOR EACH ROW
BEGIN
    SET NEW.Email = udf_normalizeEmail(NEW.Email);
    SET NEW.NoHP = udf_normalizePhone(NEW.NoHP);
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeInsertKamar;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeInsertKamar
BEFORE INSERT ON kamar
FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeUpdateKamar;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeUpdateKamar
BEFORE UPDATE ON kamar
FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeInsertRuangan;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeInsertRuangan
BEFORE INSERT ON ruangan
FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeUpdateRuangan;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeUpdateRuangan
BEFORE UPDATE ON ruangan
FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeInsertInventaris;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeInsertInventaris
BEFORE INSERT ON inventaris
FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR

DROP TRIGGER IF EXISTS trg_beforeUpdateInventaris;
-- QUERY_SEPARATOR
CREATE TRIGGER trg_beforeUpdateInventaris
BEFORE UPDATE ON inventaris
FOR EACH ROW
BEGIN
    SET NEW.UpdatedAt = NOW();
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 3. STORED PROCEDURES (sp_...)
-- -----------------------------------------------------

-- Auth Procedures
DROP PROCEDURE IF EXISTS sp_getPetugasByEmail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPetugasByEmail(IN email_param VARCHAR(100))
BEGIN
    SELECT PetugasID, NamaPetugas, Password, Jabatan FROM petugas WHERE Email = email_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPenghuniByEmail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPenghuniByEmail(IN email_param VARCHAR(100))
BEGIN
    SELECT PenghuniID, NamaPenghuni, Password FROM penghuni WHERE Email = email_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

-- Photo fetching Procedures
DROP PROCEDURE IF EXISTS sp_getFotoLaporanFromMaintenance;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getFotoLaporanFromMaintenance(IN maintenance_id INT)
BEGIN
    SELECT FotoLaporan FROM maintenance WHERE MaintenanceID = maintenance_id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getFotoMaintenanceFromMaintenance;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getFotoMaintenanceFromMaintenance(IN maintenance_id INT)
BEGIN
    SELECT FotoMaintenance FROM maintenance WHERE MaintenanceID = maintenance_id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getFotoPengambilanFromPengambilanPaket;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getFotoPengambilanFromPengambilanPaket(IN paket_id INT)
BEGIN
    SELECT FotoPengambilan FROM pengambilanpaket WHERE PaketID = paket_id;
END;
-- QUERY_SEPARATOR

-- InOutPenghuni Procedures
DROP PROCEDURE IF EXISTS sp_createInOutRequest;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createInOutRequest(
    IN penghuni_id INT,
    IN keperluan VARCHAR(100),
    IN waktu_keluar DATETIME,
    IN waktu_masuk DATETIME
)
BEGIN
    INSERT INTO inoutpenghuni (PenghuniID, Keperluan, Status, WaktuKeluar, WaktuMasuk)
    VALUES (penghuni_id, keperluan, 'Pending', waktu_keluar, waktu_masuk);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_confirmInOutExit;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_confirmInOutExit(
    IN inout_id INT,
    IN waktu_keluar DATETIME,
    IN petugas_id INT
)
BEGIN
    UPDATE inoutpenghuni
    SET Status = 'Keluar', WaktuKeluar = waktu_keluar, PetugasID = petugas_id
    WHERE InOutID = inout_id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_confirmInOutEntry;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_confirmInOutEntry(
    IN inout_id INT,
    IN waktu_masuk DATETIME
)
BEGIN
    UPDATE inoutpenghuni
    SET Status = 'Masuk', WaktuMasuk = waktu_masuk
    WHERE InOutID = inout_id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_countActiveInOutRequests;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_countActiveInOutRequests(IN penghuni_id INT)
BEGIN
    SELECT COUNT(*) as count FROM inoutpenghuni WHERE PenghuniID = penghuni_id AND Status IN ('Pending', 'Keluar');
END;
-- QUERY_SEPARATOR

-- Kamar Procedures
DROP PROCEDURE IF EXISTS sp_getAllKamarWithOccupancy;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getAllKamarWithOccupancy()
BEGIN
    SELECT
        k.KamarID,
        k.NomorKamar,
        k.KapasitasPenghuni,
        k.Lantai,
        COUNT(p.PenghuniID) AS JumlahPenghuniAktual
    FROM kamar k
    LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0
    WHERE k.IsDeleted = 0
    GROUP BY k.KamarID, k.NomorKamar, k.KapasitasPenghuni, k.Lantai
    ORDER BY k.NomorKamar ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getKamarDetailWithOccupancy;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getKamarDetailWithOccupancy(IN kamar_id INT)
BEGIN
    SELECT
        k.KamarID,
        k.NomorKamar,
        k.KapasitasPenghuni,
        k.Lantai,
        COUNT(p.PenghuniID) AS JumlahPenghuniAktual
    FROM kamar k
    LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0
    WHERE k.KamarID = kamar_id AND k.IsDeleted = 0
    GROUP BY k.KamarID, k.NomorKamar, k.KapasitasPenghuni, k.Lantai
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPenghuniByKamarId;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPenghuniByKamarId(IN kamar_id INT)
BEGIN
    SELECT PenghuniID, NamaPenghuni, Nim, JenisKelamin, NoHP, Email
    FROM penghuni
    WHERE KamarID = kamar_id AND IsDeleted = 0
    ORDER BY NamaPenghuni ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deleteKamar;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deleteKamar(IN kamar_id INT)
BEGIN
    UPDATE kamar SET IsDeleted = 1 WHERE KamarID = kamar_id;
END;
-- QUERY_SEPARATOR

-- Ruangan Procedures
DROP PROCEDURE IF EXISTS sp_getAllRuangan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getAllRuangan()
BEGIN
    SELECT * FROM ruangan WHERE IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getRuanganById;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getRuanganById(IN ruangan_id INT)
BEGIN
    SELECT * FROM ruangan WHERE RuanganID = ruangan_id LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deleteRuangan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deleteRuangan(IN ruangan_id INT)
BEGIN
    UPDATE ruangan SET IsDeleted = 1 WHERE RuanganID = ruangan_id;
END;
-- QUERY_SEPARATOR

-- Additional Procedures for Penghuni
DROP PROCEDURE IF EXISTS sp_getActivePenghuniForSelect;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getActivePenghuniForSelect()
BEGIN
    SELECT p.PenghuniID, p.NamaPenghuni, p.Nim, k.NomorKamar,
           udf_formatPenghuniLabel(p.NamaPenghuni, p.Nim, k.NomorKamar) AS OptionLabel
    FROM penghuni p
    LEFT JOIN kamar k ON p.KamarID = k.KamarID
    WHERE p.IsDeleted = 0
    ORDER BY p.NamaPenghuni;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_checkPenghuniExist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkPenghuniExist(IN id INT)
BEGIN
    SELECT PenghuniID FROM penghuni WHERE PenghuniID = id AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_createPenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createPenghuni(
    IN kamar_id INT,
    IN nama VARCHAR(100),
    IN nim VARCHAR(10),
    IN jk CHAR(1),
    IN no_hp VARCHAR(20),
    IN email VARCHAR(100),
    IN password_hash VARCHAR(100),
    IN alamat TEXT
)
BEGIN
    INSERT INTO penghuni (KamarID, NamaPenghuni, Nim, JenisKelamin, NoHP, Email, Password, Alamat, IsDeleted)
    VALUES (kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat, 0);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePenghuniWithPassword;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePenghuniWithPassword(
    IN id INT,
    IN kamar_id INT,
    IN nama VARCHAR(100),
    IN nim VARCHAR(10),
    IN jk CHAR(1),
    IN no_hp VARCHAR(20),
    IN email VARCHAR(100),
    IN password_hash VARCHAR(100),
    IN alamat TEXT
)
BEGIN
    UPDATE penghuni
    SET KamarID = kamar_id, NamaPenghuni = nama, Nim = nim, JenisKelamin = jk,
        NoHP = no_hp, Email = email, Password = password_hash, Alamat = alamat
    WHERE PenghuniID = id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePenghuniWithoutPassword;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePenghuniWithoutPassword(
    IN id INT,
    IN kamar_id INT,
    IN nama VARCHAR(100),
    IN nim VARCHAR(10),
    IN jk CHAR(1),
    IN no_hp VARCHAR(20),
    IN email VARCHAR(100),
    IN alamat TEXT
)
BEGIN
    UPDATE penghuni
    SET KamarID = kamar_id, NamaPenghuni = nama, Nim = nim, JenisKelamin = jk,
        NoHP = no_hp, Email = email, Alamat = alamat
    WHERE PenghuniID = id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deletePenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deletePenghuni(IN id INT)
BEGIN
    UPDATE penghuni SET IsDeleted = 1 WHERE PenghuniID = id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_restorePenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_restorePenghuni(
    IN id INT,
    IN kamar_id INT,
    IN nama VARCHAR(100),
    IN nim VARCHAR(10),
    IN jk CHAR(1),
    IN no_hp VARCHAR(20),
    IN email VARCHAR(100),
    IN password_hash VARCHAR(100),
    IN alamat TEXT
)
BEGIN
    UPDATE penghuni
    SET KamarID = kamar_id, NamaPenghuni = nama, Nim = nim, JenisKelamin = jk,
        NoHP = no_hp, Email = email, Password = password_hash, Alamat = alamat, IsDeleted = 0
    WHERE PenghuniID = id;
END;
-- QUERY_SEPARATOR

-- Additional Procedures for Paket
DROP PROCEDURE IF EXISTS sp_getPaketDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketDetail(IN id INT)
BEGIN
    SELECT pk.*, pt.NamaPetugas
    FROM paket pk
    JOIN petugas pt ON pk.PetugasID = pt.PetugasID
    WHERE pk.PaketID = id
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_createPaket;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createPaket(
    IN petugas_id INT,
    IN nama_pengirim VARCHAR(100),
    IN kurir VARCHAR(50),
    IN jenis_paket VARCHAR(50),
    IN waktu_sampai DATETIME,
    IN penghuni_id INT
)
BEGIN
    INSERT INTO paket (PetugasID, NamaPengirim, Kurir, JenisPaket, WaktuSampai, PenghuniID)
    VALUES (petugas_id, nama_pengirim, kurir, jenis_paket, waktu_sampai, penghuni_id);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePaket;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePaket(
    IN id INT,
    IN nama_pengirim VARCHAR(100),
    IN kurir VARCHAR(50),
    IN jenis_paket VARCHAR(50),
    IN waktu_sampai DATETIME,
    IN penghuni_id INT
)
BEGIN
    UPDATE paket
    SET NamaPengirim = nama_pengirim, Kurir = kurir, JenisPaket = jenis_paket,
        WaktuSampai = waktu_sampai, PenghuniID = penghuni_id
    WHERE PaketID = id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deletePaket;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deletePaket(IN id INT)
BEGIN
    DELETE FROM paket WHERE PaketID = id;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_countPackagePickupByPaketId;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_countPackagePickupByPaketId(IN id INT)
BEGIN
    SELECT COUNT(*) AS total FROM pengambilanpaket WHERE PaketID = id;
END;
