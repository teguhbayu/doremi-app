# Doremi App — Database Integration Guide for AI Agents

This document explains the database layer migration implemented on June 30, 2026. All inline database queries (fetching and updating) have been migrated to **Stored Procedures**, formatting/sanitization tasks have been moved to **User-Defined Functions (UDFs)**, and field constraints/auto-timestamp updates have been automated using **Triggers**.

---

## 1. Coding Guidelines for Agents
When writing code, refactoring, or introducing new database operations in this repository, follow these rules:
1. **Never write raw SELECT, INSERT, UPDATE, or DELETE queries** for entities that already have stored procedures. Instead, use prepared statements to call the stored procedures (e.g. `CALL sp_someProcedure(?, ?)`).
2. **Never manually set or pass `UpdateAt` / `UpdatedAt` timestamps** in your database updates or creations. The database triggers (`trg_before...`) handle updating these fields automatically.
3. **Use UDFs for data sanitization and display formatting**. Never manually uppercase NIMs or lowercase/trim emails in PHP queries.
4. If you write new database logic, implement it inside a Stored Procedure matching the naming conventions below.

---

## 2. User-Defined Functions (UDFs)
All UDFs are prefixed with `udf_` and are deterministic:

| Function Name | Parameters | Returns | Description |
| :--- | :--- | :--- | :--- |
| `udf_normalizeNim` | `nim VARCHAR(100)` | `VARCHAR(100)` | Trims spaces, removes nested spaces, and capitalizes student IDs. |
| `udf_normalizeEmail` | `email VARCHAR(100)` | `VARCHAR(100)` | Standardizes emails to lowercase and trims surrounding whitespace. |
| `udf_normalizePhone` | `phone VARCHAR(100)` | `VARCHAR(100)` | Strips all non-digit characters from telephone numbers. |
| `udf_formatPenghuniLabel` | `nama VARCHAR(100)`, `nim VARCHAR(10)`, `nomorKamar VARCHAR(20)` | `VARCHAR(255)` | Constructs a formatted string: `Nama (NIM) - Kamar NomorKamar`. |

---

## 3. Database Triggers
All triggers are prefixed with `trg_` and automate background data formatting and timestamp updates:

* **Resident (`penghuni`)**:
  * `trg_beforeInsertPenghuni` / `trg_beforeUpdatePenghuni`: Automatically normalizes `Nim`, `Email`, and `NoHP` columns using UDFs and sets `UpdateAt` to `NOW()`.
* **Staff (`petugas`)**:
  * `trg_beforeInsertPetugas` / `trg_beforeUpdatePetugas`: Automatically normalizes `Email` and `NoHP` columns using UDFs and sets `UpdatedAt` to `NOW()`.
* **Kamar / Ruangan / Inventaris**:
  * `trg_beforeInsertKamar` / `trg_beforeUpdateKamar` / `trg_beforeInsertRuangan` / `trg_beforeUpdateRuangan` / `trg_beforeInsertInventaris` / `trg_beforeUpdateInventaris`: Automatically sets the `UpdatedAt` field to `NOW()`.

---

## 4. Stored Procedures
All stored procedures are prefixed with `sp_` and camel-cased:

### Authentication & Identification
* `sp_getPetugasByEmail(email)`: Retrieves active staff records by email.
* `sp_getPenghuniByEmail(email)`: Retrieves active resident records by email.
* `sp_checkPenghuniExist(id)`: Verifies if a resident is active and exists.

### Image & Media Retrieval
* `sp_getFotoLaporanFromMaintenance(id)`: Retrieves report photo from maintenance ticket.
* `sp_getFotoMaintenanceFromMaintenance(id)`: Retrieves repair photo from maintenance ticket.
* `sp_getFotoPengambilanFromPengambilanPaket(id)`: Retrieves package pickup photo.

### In/Out Logs (`inoutpenghuni`)
* `sp_createInOutRequest(penghuni_id, keperluan, waktu_keluar, waktu_masuk)`: Inserts a new exit log in pending state.
* `sp_confirmInOutExit(inout_id, waktu_keluar, petugas_id)`: Registers actual departure.
* `sp_confirmInOutEntry(inout_id, waktu_masuk)`: Registers actual return.
* `sp_countActiveInOutRequests(penghuni_id)`: Checks for active departure records.

### Kamar (Room) Management
* `sp_getAllKamarWithOccupancy()`: Fetches all rooms and their current active occupancy count.
* `sp_getKamarDetailWithOccupancy(kamar_id)`: Fetches details and occupancy count of a single room.
* `sp_getPenghuniByKamarId(kamar_id)`: Lists all active residents currently living in the room.
* `sp_deleteKamar(kamar_id)`: Soft-deletes a room.

### Ruangan (Area) Management
* `sp_getAllRuangan()`: Retrieves all active rooms/areas.
* `sp_getRuanganById(ruangan_id)`: Fetches details of a specific area.
* `sp_deleteRuangan(ruangan_id)`: Soft-deletes an area.

### Penghuni (Resident) Profile Management
* `sp_getActivePenghuniForSelect()`: Retrieves all active residents formatted for drop-down selection options.
* `sp_createPenghuni(kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat)`: Inserts a new resident.
* `sp_updatePenghuniWithPassword(id, kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat)`: Updates profile details including password.
* `sp_updatePenghuniWithoutPassword(id, kamar_id, nama, nim, jk, no_hp, email, alamat)`: Updates profile details without changing password.
* `sp_deletePenghuni(id)`: Soft-deletes a resident.
* `sp_restorePenghuni(id, kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat)`: Restores a soft-deleted resident.

### Paket (Package) Management
* `sp_getPaketDetail(id)`: Retrieves details for a package.
* `sp_createPaket(petugas_id, nama_pengirim, kurir, jenis_paket, waktu_sampai, penghuni_id)`: Adds a package.
* `sp_updatePaket(id, nama_pengirim, kurir, jenis_paket, waktu_sampai, penghuni_id)`: Updates package details.
* `sp_deletePaket(id)`: Deletes a package.
* `sp_countPackagePickupByPaketId(id)`: Checks referencing package pickup records.

---

## 5. Implementation Reference
Refer to the migration script under [20260630_stored_procedures_functions_triggers.sql](file:///C:/xampp/htdocs/doremi-app/database/migrations/20260630_stored_procedures_functions_triggers.sql) for standard DDL schemas and implementation detail verification.
