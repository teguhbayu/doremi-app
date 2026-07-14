-- Doremi App Database Migrations
-- Stored Procedures Batch 2 (continuation of 20260630 migration)
-- Covers: Petugas, Ruangan, Kamar

-- -----------------------------------------------------
-- 1. PETUGAS PROCEDURES
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getAllPetugas;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getAllPetugas()
BEGIN
    SELECT * FROM petugas WHERE IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_findPetugasDuplicateActive;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_findPetugasDuplicateActive(IN email_param VARCHAR(100), IN no_hp_param VARCHAR(20))
BEGIN
    SELECT PetugasID, Email, NoHP
    FROM petugas
    WHERE IsDeleted = 0 AND (Email = email_param OR NoHP = no_hp_param)
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_findPetugasDuplicateDeleted;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_findPetugasDuplicateDeleted(IN email_param VARCHAR(100), IN no_hp_param VARCHAR(20))
BEGIN
    SELECT PetugasID, Email, NoHP
    FROM petugas
    WHERE IsDeleted = 1 AND (Email = email_param OR NoHP = no_hp_param)
    ORDER BY PetugasID ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_restorePetugas;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_restorePetugas(
    IN id_param INT,
    IN nama_param VARCHAR(100),
    IN email_param VARCHAR(100),
    IN password_param TEXT,
    IN jabatan_param VARCHAR(20),
    IN no_hp_param VARCHAR(20)
)
BEGIN
    UPDATE petugas
    SET NamaPetugas = nama_param, Email = email_param, Password = password_param,
        Jabatan = jabatan_param, NoHP = no_hp_param, IsDeleted = 0
    WHERE PetugasID = id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_createPetugas;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createPetugas(
    IN nama_param VARCHAR(100),
    IN email_param VARCHAR(100),
    IN password_param TEXT,
    IN jabatan_param VARCHAR(20),
    IN no_hp_param VARCHAR(20)
)
BEGIN
    INSERT INTO petugas (NamaPetugas, Email, Password, Jabatan, NoHP)
    VALUES (nama_param, email_param, password_param, jabatan_param, no_hp_param);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPetugasById;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPetugasById(IN id_param INT)
BEGIN
    SELECT PetugasID, NamaPetugas, Email, Jabatan, NoHP
    FROM petugas
    WHERE PetugasID = id_param AND IsDeleted = 0
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_findPetugasDuplicateExcluding;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_findPetugasDuplicateExcluding(IN id_param INT, IN email_param VARCHAR(100), IN no_hp_param VARCHAR(20))
BEGIN
    SELECT PetugasID, Email, NoHP
    FROM petugas
    WHERE IsDeleted = 0 AND PetugasID != id_param AND (Email = email_param OR NoHP = no_hp_param)
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePetugasWithPassword;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePetugasWithPassword(
    IN id_param INT,
    IN nama_param VARCHAR(100),
    IN email_param VARCHAR(100),
    IN jabatan_param VARCHAR(20),
    IN no_hp_param VARCHAR(20),
    IN password_param TEXT
)
BEGIN
    UPDATE petugas
    SET NamaPetugas = nama_param, Email = email_param, Jabatan = jabatan_param,
        NoHP = no_hp_param, Password = password_param
    WHERE PetugasID = id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePetugasWithoutPassword;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePetugasWithoutPassword(
    IN id_param INT,
    IN nama_param VARCHAR(100),
    IN email_param VARCHAR(100),
    IN jabatan_param VARCHAR(20),
    IN no_hp_param VARCHAR(20)
)
BEGIN
    UPDATE petugas
    SET NamaPetugas = nama_param, Email = email_param, Jabatan = jabatan_param, NoHP = no_hp_param
    WHERE PetugasID = id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deletePetugas;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deletePetugas(IN id_param INT)
BEGIN
    UPDATE petugas SET IsDeleted = 1 WHERE PetugasID = id_param;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 2. RUANGAN PROCEDURES (writes only; reads/delete already exist)
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_createRuangan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createRuangan(
    IN nama_param VARCHAR(100),
    IN jenis_param VARCHAR(50),
    IN lantai_param VARCHAR(30),
    IN keterangan_param TEXT
)
BEGIN
    INSERT INTO ruangan (NamaRuangan, JenisRuangan, Lantai, Keterangan, IsDeleted)
    VALUES (nama_param, jenis_param, lantai_param, keterangan_param, 0);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updateRuangan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updateRuangan(
    IN id_param INT,
    IN nama_param VARCHAR(100),
    IN jenis_param VARCHAR(50),
    IN lantai_param VARCHAR(30),
    IN keterangan_param TEXT
)
BEGIN
    UPDATE ruangan
    SET NamaRuangan = nama_param, JenisRuangan = jenis_param, Lantai = lantai_param, Keterangan = keterangan_param
    WHERE RuanganID = id_param;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 3. KAMAR PROCEDURES (create/edit; index/detail/delete already exist)
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getKamarById;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getKamarById(IN id_param INT)
BEGIN
    SELECT * FROM kamar WHERE KamarID = id_param LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_countActivePenghuniByKamar;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_countActivePenghuniByKamar(IN kamar_id_param INT)
BEGIN
    SELECT COUNT(*) AS total FROM penghuni WHERE KamarID = kamar_id_param AND IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_findKamarDuplicateNomor;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_findKamarDuplicateNomor(IN nomor_param VARCHAR(20), IN exclude_id_param INT)
BEGIN
    SELECT KamarID
    FROM kamar
    WHERE IsDeleted = 0
      AND UPPER(REPLACE(NomorKamar, ' ', '')) = nomor_param
      AND (exclude_id_param = 0 OR KamarID != exclude_id_param)
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_createKamar;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createKamar(
    IN nomor_param VARCHAR(20),
    IN kapasitas_param INT,
    IN lantai_param VARCHAR(10)
)
BEGIN
    INSERT INTO kamar (NomorKamar, KapasitasPenghuni, Lantai, IsDeleted)
    VALUES (nomor_param, kapasitas_param, lantai_param, 0);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updateKamar;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updateKamar(
    IN id_param INT,
    IN nomor_param VARCHAR(20),
    IN kapasitas_param INT,
    IN lantai_param VARCHAR(10)
)
BEGIN
    UPDATE kamar
    SET NomorKamar = nomor_param, KapasitasPenghuni = kapasitas_param, Lantai = lantai_param
    WHERE KamarID = id_param;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 4. INVENTARIS PROCEDURES
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getAllInventarisWithLokasi;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getAllInventarisWithLokasi()
BEGIN
    SELECT i.*, k.NomorKamar, r.NamaRuangan
    FROM inventaris i
    LEFT JOIN kamar k ON i.KamarID = k.KamarID
    LEFT JOIN ruangan r ON i.RuanganID = r.RuanganID
    WHERE i.IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getActiveKamarOptions;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getActiveKamarOptions()
BEGIN
    SELECT KamarID, NomorKamar FROM kamar WHERE IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getActiveRuanganOptions;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getActiveRuanganOptions()
BEGIN
    SELECT RuanganID, NamaRuangan FROM ruangan WHERE IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_checkKamarActive;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkKamarActive(IN kamar_id_param INT)
BEGIN
    SELECT KamarID FROM kamar WHERE KamarID = kamar_id_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_checkRuanganActive;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkRuanganActive(IN ruangan_id_param INT)
BEGIN
    SELECT RuanganID FROM ruangan WHERE RuanganID = ruangan_id_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInventarisById;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInventarisById(IN id_param INT)
BEGIN
    SELECT * FROM inventaris WHERE InventarisID = id_param LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_createInventaris;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createInventaris(
    IN ruangan_id_param INT,
    IN kamar_id_param INT,
    IN nama_param VARCHAR(100),
    IN jumlah_param INT,
    IN keterangan_param TEXT
)
BEGIN
    INSERT INTO inventaris (RuanganID, KamarID, NamaBarang, Jumlah, Keterangan, IsDeleted)
    VALUES (ruangan_id_param, kamar_id_param, nama_param, jumlah_param, keterangan_param, 0);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updateInventaris;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updateInventaris(
    IN id_param INT,
    IN ruangan_id_param INT,
    IN kamar_id_param INT,
    IN nama_param VARCHAR(100),
    IN jumlah_param INT,
    IN keterangan_param TEXT
)
BEGIN
    UPDATE inventaris
    SET RuanganID = ruangan_id_param, KamarID = kamar_id_param, NamaBarang = nama_param,
        Jumlah = jumlah_param, Keterangan = keterangan_param
    WHERE InventarisID = id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deleteInventaris;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deleteInventaris(IN id_param INT)
BEGIN
    UPDATE inventaris SET IsDeleted = 1 WHERE InventarisID = id_param;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 5. PENGHUNI READ PROCEDURES (writes already migrated)
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getPenghuniIdentityRows;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPenghuniIdentityRows(IN is_deleted_param TINYINT, IN exclude_id_param INT)
BEGIN
    SELECT PenghuniID, Nim, Email, NoHP
    FROM penghuni
    WHERE IsDeleted = is_deleted_param
      AND (exclude_id_param = 0 OR PenghuniID != exclude_id_param);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPenghuniList;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPenghuniList()
BEGIN
    SELECT p.*, k.NomorKamar
    FROM penghuni p
    LEFT JOIN kamar k ON p.KamarID = k.KamarID
    WHERE p.IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getKamarForPenghuniAssignment;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getKamarForPenghuniAssignment(IN kamar_id_param INT)
BEGIN
    SELECT KamarID, NomorKamar, KapasitasPenghuni, Lantai
    FROM kamar
    WHERE KamarID = kamar_id_param AND IsDeleted = 0
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPenghuniRoomOccupantSummary;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPenghuniRoomOccupantSummary(IN kamar_id_param INT, IN exclude_id_param INT)
BEGIN
    SELECT COUNT(*) AS total, GROUP_CONCAT(DISTINCT JenisKelamin ORDER BY JenisKelamin SEPARATOR ',') AS genders
    FROM penghuni
    WHERE KamarID = kamar_id_param AND IsDeleted = 0
      AND (exclude_id_param = 0 OR PenghuniID != exclude_id_param);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPenghuniByIdFull;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPenghuniByIdFull(IN id_param INT)
BEGIN
    SELECT * FROM penghuni WHERE PenghuniID = id_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getActiveKamarWithOccupancy;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getActiveKamarWithOccupancy()
BEGIN
    SELECT
        k.KamarID,
        k.NomorKamar,
        k.Lantai,
        k.KapasitasPenghuni,
        COUNT(p.PenghuniID) AS JumlahPenghuniAktual
    FROM kamar k
    LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0
    WHERE k.IsDeleted = 0
    GROUP BY k.KamarID, k.NomorKamar, k.Lantai, k.KapasitasPenghuni
    ORDER BY k.NomorKamar ASC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 6. PAKET / PENGAMBILANPAKET PROCEDURES
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getPaketListForRole;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketListForRole(IN role_param VARCHAR(20), IN user_id_param INT)
BEGIN
    SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
           pp.PengambilanPaketID, pp.Status, pp.WaktuPengambilan, pp.Keterangan, pp.HasFotoPengambilan
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    LEFT JOIN kamar k ON ph.KamarID = k.KamarID
    JOIN petugas pt ON pk.PetugasID = pt.PetugasID
    LEFT JOIN (
        SELECT pp1.PengambilanPaketID, pp1.PaketID, pp1.PenghuniID, pp1.PetugasID,
               pp1.FotoPengambilan, pp1.WaktuPengambilan, pp1.Status, pp1.Keterangan,
               (pp1.FotoPengambilan IS NOT NULL AND pp1.FotoPengambilan != '') AS HasFotoPengambilan
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE role_param = 'SIGAP' OR pk.PenghuniID = user_id_param
    ORDER BY pk.PaketID DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketWithLatestPickup;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketWithLatestPickup(IN paket_id_param INT, IN penghuni_id_param INT)
BEGIN
    SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar,
           pt.NamaPetugas AS NamaPetugasPaket,
           pp.PengambilanPaketID, pp.PetugasID AS PickupPetugasID,
           pp.FotoPengambilan, pp.WaktuPengambilan, pp.Status, pp.Keterangan
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    LEFT JOIN kamar k ON ph.KamarID = k.KamarID
    JOIN petugas pt ON pk.PetugasID = pt.PetugasID
    LEFT JOIN (
        SELECT pp1.PengambilanPaketID, pp1.PaketID, pp1.PenghuniID, pp1.PetugasID,
               pp1.FotoPengambilan, pp1.WaktuPengambilan, pp1.Status, pp1.Keterangan,
               (pp1.FotoPengambilan IS NOT NULL AND pp1.FotoPengambilan != '') AS HasFotoPengambilan
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
    WHERE pk.PaketID = paket_id_param
      AND (penghuni_id_param = 0 OR pk.PenghuniID = penghuni_id_param)
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_insertPaketPickup;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_insertPaketPickup(
    IN paket_id_param INT,
    IN penghuni_id_param INT,
    IN petugas_id_param INT,
    IN foto_param LONGTEXT,
    IN waktu_param DATETIME,
    IN status_param VARCHAR(20),
    IN keterangan_param TEXT
)
BEGIN
    INSERT INTO pengambilanpaket (PaketID, PenghuniID, PetugasID, FotoPengambilan, WaktuPengambilan, Status, Keterangan)
    VALUES (paket_id_param, penghuni_id_param, petugas_id_param, foto_param, waktu_param, status_param, keterangan_param);
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePaketPickup;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePaketPickup(
    IN id_param INT,
    IN penghuni_id_param INT,
    IN petugas_id_param INT,
    IN foto_param LONGTEXT,
    IN waktu_param DATETIME,
    IN status_param VARCHAR(20),
    IN keterangan_param TEXT
)
BEGIN
    UPDATE pengambilanpaket
    SET PenghuniID = penghuni_id_param, PetugasID = petugas_id_param, FotoPengambilan = foto_param,
        WaktuPengambilan = waktu_param, Status = status_param, Keterangan = keterangan_param
    WHERE PengambilanPaketID = id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_updatePaketPickupReview;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_updatePaketPickupReview(
    IN id_param INT,
    IN petugas_id_param INT,
    IN status_param VARCHAR(20),
    IN keterangan_param TEXT
)
BEGIN
    UPDATE pengambilanpaket
    SET PetugasID = petugas_id_param, Status = status_param, Keterangan = keterangan_param
    WHERE PengambilanPaketID = id_param;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 7. INOUT READ PROCEDURES (writes already migrated)
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getInOutHistoryForPenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutHistoryForPenghuni(IN penghuni_id_param INT)
BEGIN
    SELECT io.*, p.NamaPetugas
    FROM inoutpenghuni io
    LEFT JOIN petugas p ON io.PetugasID = p.PetugasID
    WHERE io.PenghuniID = penghuni_id_param
    ORDER BY io.InOutID DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPendingInOutRequests;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPendingInOutRequests()
BEGIN
    SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar
    FROM inoutpenghuni io
    JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID
    JOIN kamar k ON pe.KamarID = k.KamarID
    WHERE io.Status = 'Pending'
    ORDER BY io.InOutID ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getOutsideInOutRequests;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getOutsideInOutRequests()
BEGIN
    SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar
    FROM inoutpenghuni io
    JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID
    JOIN kamar k ON pe.KamarID = k.KamarID
    WHERE io.Status = 'Keluar'
    ORDER BY io.WaktuKeluar ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getAllInOutLogs;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getAllInOutLogs()
BEGIN
    SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar, pt.NamaPetugas
    FROM inoutpenghuni io
    JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID
    JOIN kamar k ON pe.KamarID = k.KamarID
    LEFT JOIN petugas pt ON io.PetugasID = pt.PetugasID
    ORDER BY io.InOutID DESC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 8. MAINTENANCE PROCEDURES (reads + writes)
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportsForRole;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportsForRole(IN role_param VARCHAR(20), IN user_id_param INT)
BEGIN
    SELECT m.MaintenanceID, m.PenghuniID, m.PetugasID, m.TeknisiID, m.RuanganID, m.InventarisID,
           m.TanggalLapor, m.JenisLaporan, m.Deskripsi, m.StatusMaintenance,
           m.TanggalSelesai, m.Keterangan,
           (m.FotoLaporan IS NOT NULL AND m.FotoLaporan != '') AS HasFotoLaporan,
           (m.FotoMaintenance IS NOT NULL AND m.FotoMaintenance != '') AS HasFotoMaintenance,
           p.NamaPenghuni, p.Nim,
           pt.NamaPetugas AS NamaReporterPetugas,
           tech.NamaPetugas AS NamaTeknisi,
           r.NamaRuangan, r.Lantai AS LantaiRuangan,
           i.NamaBarang,
           inv_r.NamaRuangan AS InvRuanganNama, inv_k.NomorKamar AS InvKamarNomor
    FROM maintenance m
    LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
    LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
    LEFT JOIN petugas tech ON m.TeknisiID = tech.PetugasID
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    LEFT JOIN ruangan inv_r ON i.RuanganID = inv_r.RuanganID
    LEFT JOIN kamar inv_k ON i.KamarID = inv_k.KamarID
    WHERE m.IsDeleted = 0
      AND (
          role_param = 'MAINTENANCE'
          OR (role_param = 'PENGHUNI' AND m.PenghuniID = user_id_param)
          OR (role_param NOT IN ('MAINTENANCE', 'PENGHUNI') AND m.PetugasID = user_id_param AND m.PenghuniID IS NULL)
      )
    ORDER BY CASE WHEN m.JenisLaporan = 'Kerusakan Darurat / Berat' THEN 1
                  WHEN m.JenisLaporan = 'Kerusakan Sedang' THEN 2
                  ELSE 3 END, m.MaintenanceID DESC;
END;
-- QUERY_SEPARATOR


DROP PROCEDURE IF EXISTS sp_getMaintenanceReportById;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportById(IN id_param INT)
BEGIN
    SELECT * FROM maintenance WHERE MaintenanceID = id_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceRooms;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceRooms(IN only_active_param TINYINT)
BEGIN
    SELECT RuanganID, NamaRuangan, Lantai
    FROM ruangan
    WHERE only_active_param = 0 OR IsDeleted = 0
    ORDER BY Lantai ASC, NamaRuangan ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceInventory;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceInventory(IN only_active_param TINYINT)
BEGIN
    SELECT InventarisID, NamaBarang
    FROM inventaris
    WHERE only_active_param = 0 OR IsDeleted = 0
    ORDER BY NamaBarang ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_checkInventarisActive;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkInventarisActive(IN inventaris_id_param INT)
BEGIN
    SELECT InventarisID FROM inventaris WHERE InventarisID = inventaris_id_param AND IsDeleted = 0 LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_createMaintenanceReport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_createMaintenanceReport(
    IN penghuni_id_param INT,
    IN petugas_id_param INT,
    IN ruangan_id_param INT,
    IN inventaris_id_param INT,
    IN jenis_laporan_param VARCHAR(50),
    IN deskripsi_param TEXT,
    IN foto_laporan_param LONGTEXT,
    IN tanggal_lapor_param DATETIME
)
BEGIN
    INSERT INTO maintenance (
        PenghuniID, PetugasID, RuanganID, InventarisID,
        JenisLaporan, Deskripsi, FotoLaporan, TanggalLapor, StatusMaintenance
    ) VALUES (
        penghuni_id_param, petugas_id_param, ruangan_id_param, inventaris_id_param,
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
    IN jenis_laporan_param VARCHAR(50),
    IN deskripsi_param TEXT,
    IN foto_laporan_param LONGTEXT
)
BEGIN
    UPDATE maintenance
    SET RuanganID = ruangan_id_param, InventarisID = inventaris_id_param, JenisLaporan = jenis_laporan_param,
        Deskripsi = deskripsi_param, FotoLaporan = foto_laporan_param
    WHERE MaintenanceID = id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_claimMaintenanceReport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_claimMaintenanceReport(IN petugas_id_param INT, IN id_param INT)
BEGIN
    UPDATE maintenance
    SET StatusMaintenance = 'Diproses', TeknisiID = petugas_id_param
    WHERE MaintenanceID = id_param AND IsDeleted = 0 AND StatusMaintenance = 'Diajukan';
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_checkMaintenanceTechnicianOwnership;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_checkMaintenanceTechnicianOwnership(IN id_param INT, IN petugas_id_param INT)
BEGIN
    SELECT MaintenanceID
    FROM maintenance
    WHERE MaintenanceID = id_param AND TeknisiID = petugas_id_param AND StatusMaintenance = 'Diproses' AND IsDeleted = 0
    LIMIT 1;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_completeMaintenanceReport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_completeMaintenanceReport(
    IN tanggal_selesai_param DATETIME,
    IN keterangan_param TEXT,
    IN foto_maintenance_param LONGTEXT,
    IN id_param INT
)
BEGIN
    UPDATE maintenance
    SET StatusMaintenance = 'Selesai', TanggalSelesai = tanggal_selesai_param,
        Keterangan = keterangan_param, FotoMaintenance = foto_maintenance_param
    WHERE MaintenanceID = id_param AND IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_deleteMaintenanceReport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_deleteMaintenanceReport(IN id_param INT)
BEGIN
    UPDATE maintenance SET IsDeleted = 1 WHERE MaintenanceID = id_param;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 9. PAKET REPORT PROCEDURES (dashboard/paket/report.php)
-- All date filters use range_param ('7d'|'30d'|'6m'|'all') against pk.WaktuSampai
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getPaketReportExport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportExport(IN range_param VARCHAR(3))
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
       OR range_param = 'all'
    ORDER BY pk.WaktuSampai DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportStats(IN range_param VARCHAR(3))
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
       OR range_param = 'all';
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportStatusDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportStatusDist(IN range_param VARCHAR(3))
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
       OR range_param = 'all'
    GROUP BY s;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTrendDaily(IN range_param VARCHAR(3))
BEGIN
    SELECT DATE(pk.WaktuSampai) AS d, COUNT(*) AS n
    FROM paket pk
    WHERE pk.WaktuSampai IS NOT NULL
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
      )
    GROUP BY d
    ORDER BY d ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTrendMonthly(IN range_param VARCHAR(3))
BEGIN
    SELECT DATE_FORMAT(pk.WaktuSampai, '%Y-%m') AS m, COUNT(*) AS n
    FROM paket pk
    WHERE pk.WaktuSampai IS NOT NULL
      AND (
          (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY m
    ORDER BY m ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTipeDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTipeDist(IN range_param VARCHAR(3))
BEGIN
    SELECT pk.JenisPaket AS j, COUNT(*) AS n
    FROM paket pk
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR range_param = 'all'
    GROUP BY j;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTopKurir;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTopKurir(IN range_param VARCHAR(3))
BEGIN
    SELECT pk.Kurir, COUNT(*) AS n
    FROM paket pk
    WHERE pk.Kurir <> ''
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY pk.Kurir
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportJamSibuk;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportJamSibuk(IN range_param VARCHAR(3))
BEGIN
    SELECT HOUR(pk.WaktuSampai) AS h, COUNT(*) AS n
    FROM paket pk
    WHERE pk.WaktuSampai IS NOT NULL
      AND (
          (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY h;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportTopPenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportTopPenghuni(IN range_param VARCHAR(3))
BEGIN
    SELECT ph.NamaPenghuni, COUNT(*) AS n
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    WHERE (range_param = '7d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR range_param = 'all'
    GROUP BY pk.PenghuniID, ph.NamaPenghuni
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportPetugasRanking;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportPetugasRanking(IN range_param VARCHAR(3))
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
          OR range_param = 'all'
      )
    GROUP BY pk.PetugasID, pt.NamaPetugas
    ORDER BY total DESC, sudah DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getPaketReportDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getPaketReportDetail(IN range_param VARCHAR(3))
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
       OR range_param = 'all'
    ORDER BY pk.WaktuSampai DESC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 10. INOUT REPORT PROCEDURES (dashboard/inout/report.php)
-- All date filters use range_param against io.WaktuKeluar
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getInOutReportExport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportExport(IN range_param VARCHAR(3))
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
       OR range_param = 'all'
    ORDER BY io.WaktuKeluar DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportStats(IN range_param VARCHAR(3))
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
       OR range_param = 'all';
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportStatusDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportStatusDist(IN range_param VARCHAR(3))
BEGIN
    SELECT io.Status, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR range_param = 'all'
    GROUP BY io.Status;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTrendDaily(IN range_param VARCHAR(3))
BEGIN
    SELECT DATE(io.WaktuKeluar) AS d, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
    GROUP BY d
    ORDER BY d ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTrendMonthly(IN range_param VARCHAR(3))
BEGIN
    SELECT DATE_FORMAT(io.WaktuKeluar, '%Y-%m') AS m, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR range_param = 'all'
    GROUP BY m
    ORDER BY m ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportPeakHour;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportPeakHour(IN range_param VARCHAR(3))
BEGIN
    SELECT HOUR(io.WaktuKeluar) AS h, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE io.Status IN ('Keluar', 'Masuk')
      AND (
          (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY h;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTopPenghuni;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTopPenghuni(IN range_param VARCHAR(3))
BEGIN
    SELECT p.NamaPenghuni, COUNT(*) AS n
    FROM inoutpenghuni io
    JOIN penghuni p ON io.PenghuniID = p.PenghuniID
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR range_param = 'all'
    GROUP BY io.PenghuniID, p.NamaPenghuni
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportGenderDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportGenderDist(IN range_param VARCHAR(3))
BEGIN
    SELECT p.JenisKelamin AS g, COUNT(*) AS n
    FROM inoutpenghuni io
    JOIN penghuni p ON io.PenghuniID = p.PenghuniID
    WHERE (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
       OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
       OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
       OR range_param = 'all'
    GROUP BY g;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportTopKeperluan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportTopKeperluan(IN range_param VARCHAR(3))
BEGIN
    SELECT io.Keperluan, COUNT(*) AS n
    FROM inoutpenghuni io
    WHERE io.Keperluan <> ''
      AND (
          (range_param = '7d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND io.WaktuKeluar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY io.Keperluan
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportPetugasRanking;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportPetugasRanking(IN range_param VARCHAR(3))
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
          OR range_param = 'all'
      )
    GROUP BY io.PetugasID, pt.NamaPetugas
    ORDER BY total DESC, selesai DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getInOutReportDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getInOutReportDetail(IN range_param VARCHAR(3))
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
       OR range_param = 'all'
    ORDER BY io.WaktuKeluar DESC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 11. MAINTENANCE REPORT PROCEDURES (dashboard/maintenance/report.php)
-- All date filters use range_param against m.TanggalLapor, always m.IsDeleted = 0
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportExport;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportExport(IN range_param VARCHAR(3))
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
    LEFT JOIN petugas tech ON m.PetugasID = tech.PetugasID
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    ORDER BY m.TanggalLapor DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportStats(IN range_param VARCHAR(3))
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
          OR range_param = 'all'
      );
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportPriorityDist;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportPriorityDist(IN range_param VARCHAR(3))
BEGIN
    SELECT m.JenisLaporan, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY m.JenisLaporan;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportTrendDaily(IN range_param VARCHAR(3))
BEGIN
    SELECT DATE(m.TanggalLapor) AS d, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
      )
    GROUP BY d
    ORDER BY d ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportTrendMonthly(IN range_param VARCHAR(3))
BEGIN
    SELECT DATE_FORMAT(m.TanggalLapor, '%Y-%m') AS bulan, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY bulan
    ORDER BY bulan ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportTopRuangan;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportTopRuangan(IN range_param VARCHAR(3))
BEGIN
    SELECT r.NamaRuangan, COUNT(*) AS n
    FROM maintenance m
    JOIN ruangan r ON m.RuanganID = r.RuanganID
    WHERE m.RuanganID IS NOT NULL AND m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY m.RuanganID, r.NamaRuangan
    ORDER BY n DESC LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportStackedStatus;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportStackedStatus(IN range_param VARCHAR(3))
BEGIN
    SELECT m.JenisLaporan, m.StatusMaintenance, COUNT(*) AS n
    FROM maintenance m
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY m.JenisLaporan, m.StatusMaintenance;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportPetugasRanking;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportPetugasRanking(IN range_param VARCHAR(3))
BEGIN
    SELECT pt.NamaPetugas,
           COUNT(m.MaintenanceID) AS total,
           SUM(CASE WHEN m.StatusMaintenance = 'Selesai' THEN 1 ELSE 0 END) AS selesai,
           SUM(CASE WHEN m.StatusMaintenance = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
           ROUND(AVG(CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
               THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END), 1) AS avg_hari
    FROM maintenance m
    JOIN petugas pt ON m.PetugasID = pt.PetugasID
    WHERE m.PetugasID IS NOT NULL AND pt.Jabatan = 'MAINTENANCE' AND pt.IsDeleted = 0 AND m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    GROUP BY m.PetugasID, pt.NamaPetugas
    ORDER BY selesai DESC, total DESC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceReportDetail;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceReportDetail(IN range_param VARCHAR(3))
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
    LEFT JOIN petugas tech ON m.PetugasID = tech.PetugasID
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    WHERE m.IsDeleted = 0
      AND (
          (range_param = '7d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY))
          OR (range_param = '30d' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY))
          OR (range_param = '6m' AND m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH))
          OR range_param = 'all'
      )
    ORDER BY m.TanggalLapor DESC;
END;
-- QUERY_SEPARATOR


-- -----------------------------------------------------
-- 12. DASHBOARD PROCEDURES (dashboard/index.php)
-- -----------------------------------------------------

DROP PROCEDURE IF EXISTS sp_getDashboardPengurusStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardPengurusStats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM penghuni WHERE IsDeleted = 0 AND IsActive = 1) AS activePenghuni,
        (SELECT COUNT(*) FROM inoutpenghuni WHERE Status = 'Pending') AS pendingInOut,
        (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Diajukan' AND IsDeleted = 0) AS pendingMaintenance,
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
        ) AS pendingPackagePickup;
END;
-- QUERY_SEPARATOR

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

DROP PROCEDURE IF EXISTS sp_getDashboardGenderStats;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardGenderStats()
BEGIN
    SELECT JenisKelamin, COUNT(*) AS count FROM penghuni WHERE IsDeleted = 0 GROUP BY JenisKelamin;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getDashboardPenghuniIzinAktif;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardPenghuniIzinAktif(IN user_id_param INT)
BEGIN
    SELECT COUNT(*) AS total FROM inoutpenghuni WHERE PenghuniID = user_id_param AND Status IN ('Pending', 'Keluar');
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getDashboardPenghuniPaketSummary;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardPenghuniPaketSummary(IN user_id_param INT)
BEGIN
    SELECT COALESCE(pp.Status, 'Belum Diambil') AS Status
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
    WHERE pk.PenghuniID = user_id_param;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getDashboardPenghuniMaintenanceSummary;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardPenghuniMaintenanceSummary(IN user_id_param INT)
BEGIN
    SELECT StatusMaintenance
    FROM maintenance
    WHERE PenghuniID = user_id_param AND IsDeleted = 0;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getDashboardMaintenanceCounts;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardMaintenanceCounts(IN user_id_param INT)
BEGIN
    SELECT
        (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Diajukan' AND IsDeleted = 0) AS pendingTasks,
        (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Diproses' AND TeknisiID = user_id_param AND IsDeleted = 0) AS myOngoingTasks,
        (SELECT COUNT(*) FROM maintenance WHERE StatusMaintenance = 'Selesai' AND TeknisiID = user_id_param AND IsDeleted = 0) AS myCompletedTasks,
        (SELECT COUNT(*) FROM maintenance WHERE JenisLaporan = 'Kerusakan Darurat / Berat' AND StatusMaintenance != 'Selesai' AND IsDeleted = 0) AS activeEmergencyTasks;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getDashboardMyTasks;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardMyTasks(IN user_id_param INT)
BEGIN
    SELECT m.MaintenanceID, m.JenisLaporan, m.Deskripsi, m.TanggalLapor,
           r.NamaRuangan, r.Lantai AS LantaiRuangan,
           p.NamaPenghuni, pt.NamaPetugas AS NamaReporterPetugas, i.NamaBarang
    FROM maintenance m
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
    LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    WHERE m.StatusMaintenance = 'Diproses' AND m.TeknisiID = user_id_param AND m.IsDeleted = 0
    ORDER BY m.MaintenanceID DESC
    LIMIT 5;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getDashboardEmergencyList;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getDashboardEmergencyList()
BEGIN
    SELECT m.MaintenanceID, m.Deskripsi, m.StatusMaintenance, m.TanggalLapor,
           r.NamaRuangan, r.Lantai AS LantaiRuangan,
           p.NamaPenghuni, pt.NamaPetugas AS NamaReporterPetugas, i.NamaBarang
    FROM maintenance m
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
    LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    WHERE m.JenisLaporan = 'Kerusakan Darurat / Berat' AND m.StatusMaintenance != 'Selesai' AND m.IsDeleted = 0
    ORDER BY m.TanggalLapor ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceStatusPie;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceStatusPie()
BEGIN
    SELECT StatusMaintenance, COUNT(*) AS total
    FROM maintenance
    WHERE StatusMaintenance IN ('Diajukan', 'Diproses', 'Selesai') AND IsDeleted = 0
    GROUP BY StatusMaintenance;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceTrendDaily;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceTrendDaily(IN interval_days_param INT)
BEGIN
    SELECT DATE(TanggalLapor) AS hari, COUNT(*) AS total
    FROM maintenance
    WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL interval_days_param DAY) AND IsDeleted = 0
    GROUP BY hari
    ORDER BY hari ASC;
END;
-- QUERY_SEPARATOR

DROP PROCEDURE IF EXISTS sp_getMaintenanceTrendMonthly;
-- QUERY_SEPARATOR
CREATE PROCEDURE sp_getMaintenanceTrendMonthly()
BEGIN
    SELECT DATE_FORMAT(TanggalLapor, '%Y-%m') AS bulan, COUNT(*) AS total
    FROM maintenance
    WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) AND IsDeleted = 0
    GROUP BY bulan
    ORDER BY bulan ASC;
END;
-- QUERY_SEPARATOR
