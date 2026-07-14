---
name: doremi-app-context
description: Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.
alwaysApply: true
---

# Doremi — Dormitory Management App

## Project Overview

Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.

As of **June 30, 2026** (batch 1) and **July 9, 2026** (batch 2, completing the migration), the database layer has been fully migrated away from inline queries: **every** CRUD and reporting query in the app now goes through **Stored Procedures**, formatting/sanitization is handled by **User-Defined Functions (UDFs)**, and field constraints/auto-timestamps are automated via **Triggers**. There is no raw `SELECT`/`INSERT`/`UPDATE`/`DELETE` left in any request-flow PHP file (the only exception is `database/dump_to_md.php`, a dev-only CLI schema-dump tool outside the app's request flow). See [Database Layer](#database-layer-stored-procedures-udfs--triggers) below before writing any new database code.

---

## Tech Stack

- **Backend**: PHP (native, no framework)
- **Frontend**: Tailwind CSS 4.0 with `tw` prefix + Bootstrap (for components like forms, tables, modals) + Alpine.js when reactivity is needed
- **Database**: MySQL 8.0, accessed via `mysqli_*` functions — **but only by calling stored procedures**, not raw SQL (see below)
- **DB connection**: `$db` variable from `require_once 'db.php'`
- **Validation**: `Respect\Validation` (`use Respect\Validation\Validator as v;`)
- **Passwords**: `password_hash($pass, PASSWORD_BCRYPT)` / `password_verify()`
- **Auth**: PHP Sessions

---

## Current PHP Module Layout

As of **July 7, 2026**, page controllers should stay thin. Put reusable fetching, validation, formatting, and auth logic in small function files whose folder matches the app area.

### Shared Helpers

- `database/query.php` - shared `mysqli` helpers: `dbFetchAll`, `dbFetchOne`, `dbFetchValue`, `dbExecute`, and stored-procedure result cleanup.
- `utils/format.php` - generic non-database formatting helpers such as `formatDateTime`, `normalizeDateTimeForSql`, `normalizeDateTimeInputValue`, and `textLength`.

Do not put generic formatting helpers under `database/`; keep them in `utils/`.

### Domain Database Helpers

Place database-facing functions under `database/<part>.php`, using camelCase function names:

- `database/auth.php` - `fetchPetugasByEmail`, `fetchPenghuniByEmail`, `findAuthUserByEmail`.
- `database/petugas.php` - staff list/detail fetches, duplicate-active/duplicate-deleted checks, create/update(with or without password)/restore/delete procedure calls.
- `database/ruangan.php` - `createRuangan`, `updateRuangan` (reads/delete are called directly from the page via `CALL sp_getAllRuangan()` / `sp_getRuanganById()` / `sp_deleteRuangan()`).
- `database/kamar.php` - room detail fetch, active-occupant count, duplicate-nomor check, create/update procedure calls (index/detail/delete are called directly from the page via existing procedures).
- `database/inventaris.php` - full inventory CRUD, active kamar/ruangan option lists, kamar/ruangan-active existence checks used for location validation.
- `database/paket.php` - package list/detail (including latest-pickup join), package CRUD procedure calls, pickup insert/update/review operations, package photo fetching.
- `database/inout.php` - in/out list/history fetches and stored-procedure actions.
- `database/maintenance.php` - maintenance list/detail fetches, room/inventory option fetches, target-existence and technician-ownership checks, create/update/claim/complete/delete actions, photo fetching.
- `database/penghuni.php` - resident fetches (identity rows, by-id, list, room-occupancy, room-assignment, room-occupant-summary), create/update/restore procedure calls.
- `database/dashboard.php` - all `dashboard/index.php` stat/summary/trend fetches, split one function per stored procedure (role-scoped counts, gender stats, per-resident summaries, maintenance-team counts/tasks/emergency list/trend charts).
- `database/paketReport.php` / `database/inoutReport.php` / `database/maintenanceReport.php` - one function per report-page query (export, stats, distributions, trends, top-N rankings, detail rows), each taking the page's `$range` (`'7d'|'30d'|'6m'|'all'`) and forwarding it to the matching stored procedure, which applies the date-range filter server-side.
- `database/photos.php` - dispatches photo requests by type to the correct domain helper.

Pages should call these helpers instead of embedding `mysqli_prepare`, `mysqli_query`, or repeated SQL directly in the page.

### Validation Helpers

Put input collection and validation in the app area's own `validation.php` file:

- `auth/validation.php` - login input collection and validation.
- `dashboard/penghuni/validation.php` - resident create/edit validation.
- `dashboard/paket/validation.php` - package create/edit/review validation.
- `dashboard/inout/validation.php` - in/out request validation and date-time construction.
- `dashboard/maintenance/validation.php` - maintenance report create/edit validation and target ID resolution.

Validation functions should return `null` on success or an Indonesian error message string on failure. Page controllers should redirect after receiving a validation error.

### Auth Helpers

- `auth/helpers.php` owns session setup and login redirects: `authSetUserSession`, `authAttemptPasswordLogin`, `authAttemptEmailLogin`, `authRedirectToDashboard`, `authRedirectToLoginError`.
- `login.php` should collect and validate credentials, call `authAttemptPasswordLogin`, set the session, then redirect.
- `auth/callback.php` should call `authAttemptEmailLogin` for Google login, set the session, then redirect.

---

## CSS / Styling Rules

**All CSS edits must be made in `index.css` only.** This file is the Tailwind CSS v4 source and is compiled by the Tailwind CLI into `css/main.css`. Never edit `css/main.css` directly — it is a generated file and any manual changes will be overwritten on the next build.

- Source file: `index.css` (project root)
- Compiled output: `css/main.css` (do not touch)
- Tailwind config is embedded in `index.css` via `@theme { ... }` — add new design tokens there
- All Tailwind utilities are prefixed with `tw:` (e.g. `tw:flex`, `tw:text-sm`)
- The `important` modifier is active globally, so all `tw:*` utilities emit `!important`
- Bootstrap CSS is loaded separately from CDN (`head.php`) — override Bootstrap variables via `:root { --bs-... }` inside `index.css`
- After editing `index.css`, run the Tailwind CLI to regenerate `css/main.css` before testing

---

## Design

The UI must look **modern and stylish**. Always layer Tailwind utility classes over Bootstrap defaults for a polished result.

### Color Palette

```css
--color-primary: #146c94;
--color-secondary: #19a7ce;
--color-accent: #afd3e2;
--color-background: #f6f1f1;
--color-tertiary: #2380a5;
```

- Page backgrounds → `--color-background`
- Primary actions/buttons → `--color-primary`
- Hover/interactive states → `--color-secondary` or `--color-tertiary`
- Subtle highlights/badges → `--color-accent`

---

## Authentication

Call `session_start()` at the top of every page.

Current login implementation uses helper functions: `authAttemptPasswordLogin()` for password login and `authAttemptEmailLogin()` for Google login. These helpers call `database/auth.php`, which wraps `sp_getPetugasByEmail(email)` and `sp_getPenghuniByEmail(email)`.

| Session Key             | Value                                            |
| ----------------------- | ------------------------------------------------ |
| `$_SESSION['userId']`   | PetugasID (staff roles) or PenghuniID (resident) |
| `$_SESSION['userName']` | NamaPetugas or NamaPenghuni                      |
| `$_SESSION['userRole']` | One of the 5 roles below                         |

### Roles

| Role          | Source            | Notes                                       |
| ------------- | ----------------- | -------------------------------------------- |
| `PENGURUS`    | `petugas.Jabatan` | Admin/manager                               |
| `SIGAP`       | `petugas.Jabatan` | Security staff                              |
| `VIRTUS`      | `petugas.Jabatan` | General staff                               |
| `MAINTENANCE` | `petugas.Jabatan` | Maintenance staff                           |
| `PENGHUNI`    | hardcoded string  | Resident — no column in DB, assigned in PHP |

**Login flow**: check `petugas` table first → if email not found, check `penghuni` → if neither matches, return error. Use `sp_getPetugasByEmail(email)` and `sp_getPenghuniByEmail(email)` rather than writing this lookup as raw SQL.

---

## Database Schema

### `petugas`

| Column        | Type                                             | Notes                      |
| ------------- | ------------------------------------------------ | -------------------------- |
| `PetugasID`   | int PK AI                                         |                             |
| `NamaPetugas` | varchar(100)                                      |                             |
| `Email`       | varchar(100) UNIQUE                               |                             |
| `Password`    | text                                               | bcrypt                      |
| `Jabatan`     | enum('PENGURUS','SIGAP','VIRTUS','MAINTENANCE')   |                             |
| `NoHP`        | varchar(20)                                       |                             |
| `UpdatedAt`   | datetime                                          | DEFAULT CURRENT_TIMESTAMP; auto-set by `trg_beforeInsertPetugas`/`trg_beforeUpdatePetugas` — never set manually |
| `IsDeleted`   | tinyint(1)                                        | Soft delete, default 0     |

### `penghuni`

| Column         | Type                | Notes                                                                   |
| -------------- | ------------------- | ------------------------------------------------------------------------ |
| `PenghuniID`   | int PK AI           |                                                                            |
| `KamarID`      | int FK → kamar      |                                                                            |
| `NamaPenghuni` | varchar(100)        |                                                                            |
| `Nim`          | varchar(10)         | Student ID — normalized by `udf_normalizeNim` via insert/update triggers |
| `JenisKelamin` | enum('L','P')       |                                                                            |
| `NoHP`         | varchar(20)         | Normalized by `udf_normalizePhone` via triggers                          |
| `Email`        | varchar(100) UNIQUE | Normalized by `udf_normalizeEmail` via triggers                          |
| `Password`     | varchar(100)        | bcrypt                                                                    |
| `Alamat`       | text                |                                                                            |
| `IsActive`     | tinyint(1)          | default 1                                                                 |
| `UpdateAt`     | datetime            | Auto-set by `trg_beforeInsertPenghuni`/`trg_beforeUpdatePenghuni` — never set manually |
| `IsDeleted`    | tinyint(1)          | default 0                                                                 |

### `kamar`

| Column              | Type        | Notes                                                                |
| ------------------- | ----------- | --------------------------------------------------------------------- |
| `KamarID`           | int PK AI   |                                                                         |
| `NomorKamar`        | varchar(2)  | e.g. `"1A"` — lantai digit + one letter, built by `kamar_build_nomor()` in `dashboard/kamar/helpers.php` |
| `KapasitasPenghuni` | int         | Room capacity                                                          |
| `UpdatedAt`         | datetime    | Auto-set by `trg_beforeInsertKamar`/`trg_beforeUpdateKamar`            |
| `IsDeleted`         | tinyint(1)  |                                                                         |

### `ruangan`

| Column         | Type                               | Notes                                                                  |
| -------------- | ----------------------------------- | ------------------------------------------------------------------------ |
| `RuanganID`    | int PK AI                           |                                                                            |
| `NamaRuangan`  | varchar(100)                        |                                                                            |
| `JenisRuangan` | varchar(50)                         |                                                                            |
| `Keterangan`   | text                                 |                                                                            |
| `UpdatedAt`    | datetime                            | Auto-set by `trg_beforeInsertRuangan`/`trg_beforeUpdateRuangan`           |
| `IsDeleted`    | tinyint(1)                          |                                                                            |
| `Lantai`       | enum('1','2','3','4','5','6','7')   |                                                                            |

### `inventaris`

| Column         | Type             | Notes                                                                |
| -------------- | ---------------- | ----------------------------------------------------------------------- |
| `InventarisID` | int PK AI        |                                                                            |
| `RuanganID`    | int FK → ruangan |                                                                            |
| `KamarID`      | int FK → kamar   |                                                                            |
| `NamaBarang`   | varchar(100)     |                                                                            |
| `Jumlah`       | int              |                                                                            |
| `Keterangan`   | text             |                                                                            |
| `UpdatedAt`    | datetime         | Auto-set by `trg_beforeInsertInventaris`/`trg_beforeUpdateInventaris`     |
| `IsDeleted`    | tinyint(1)       |                                                                            |

### `paket`

| Column         | Type              | Notes                  |
| -------------- | ------------------ | ----------------------- |
| `PaketID`      | int PK AI          |                          |
| `PetugasID`    | int FK → petugas   | Staff who received it   |
| `PenghuniID`   | int FK → penghuni  | Intended recipient      |
| `NamaPengirim` | varchar(100)       |                          |
| `Kurir`        | varchar(50)        | Courier name            |
| `WaktuSampai`  | datetime           |                          |

### `pengambilanpaket`

| Column               | Type                                   | Notes |
| -------------------- | --------------------------------------- | ----- |
| `PengambilanPaketID` | int PK AI                               |       |
| `PaketID`            | int FK → paket                          |       |
| `PenghuniID`         | int FK → penghuni                       |       |
| `PetugasID`          | int FK → petugas                        |       |
| `FotoPengambilan`    | longtext                                |       |
| `WaktuPengambilan`   | datetime                                |       |
| `Status`             | enum('Belum Diambil','Sudah Diambil')   |       |
| `Keterangan`         | text                                     |       |

### `inoutpenghuni`

| Column        | Type                               | Notes           |
| ------------- | ----------------------------------- | ----------------- |
| `InOutID`     | int PK AI                          |                    |
| `PenghuniID`  | int FK → penghuni                  |                    |
| `PetugasID`   | int FK → petugas                   | Guard on duty      |
| `WaktuKeluar` | datetime                           | Nullable           |
| `WaktuMasuk`  | datetime                           |                    |
| `Keperluan`   | varchar(100)                       | Purpose of exit    |
| `Status`      | enum('Masuk','Keluar','Pending')   |                    |

### `maintenance`

| Column              | Type                                              | Notes          |
| ------------------- | --------------------------------------------------- | -------------- |
| `MaintenanceID`     | int PK AI                                            |                |
| `PenghuniID`        | int FK → penghuni                                    | Reporter       |
| `PetugasID`         | int FK → petugas                                     | Assigned staff |
| `RuanganID`         | int FK → ruangan                                     |                |
| `InventarisID`      | int FK → inventaris                                  |                |
| `TanggalLapor`      | datetime                                             |                |
| `JenisLaporan`      | enum('Kerusakan Ringan','Kerusakan Sedang','Kerusakan Darurat / Berat') | Priority scale, not a category — used to sort/prioritize reports |
| `Deskripsi`         | text                                                  |                |
| `StatusMaintenance` | enum('Diajukan','Diproses','Selesai','Ditolak')      |                |
| `TanggalSelesai`    | datetime NULL                                        |                |
| `Keterangan`        | text NULL                                            |                |
| `FotoLaporan`       | longtext                                              |                |
| `FotoMaintenance`   | longtext                                              |                |

---

## Database Layer: Stored Procedures, UDFs & Triggers

The database layer migration (June 30, 2026, completed July 9, 2026) replaced inline queries with three layers: stored procedures for CRUD, UDFs for sanitization/formatting, and triggers for automatic constraints/timestamps. New database logic should be implemented at this layer, not as raw inline SQL.

### User-Defined Functions (UDFs)

All UDFs are prefixed with `udf_` and are deterministic:

| Function Name             | Parameters                                                             | Returns       | Description                                                            |
| -------------------------- | ------------------------------------------------------------------------ | -------------- | ------------------------------------------------------------------------ |
| `udf_normalizeNim`         | `nim VARCHAR(100)`                                                       | `VARCHAR(100)` | Trims spaces, removes nested spaces, capitalizes student IDs.            |
| `udf_normalizeEmail`       | `email VARCHAR(100)`                                                     | `VARCHAR(100)` | Standardizes emails to lowercase and trims surrounding whitespace.       |
| `udf_normalizePhone`       | `phone VARCHAR(100)`                                                     | `VARCHAR(100)` | Strips all non-digit characters from telephone numbers.                 |
| `udf_formatPenghuniLabel`  | `nama VARCHAR(100)`, `nim VARCHAR(10)`, `nomorKamar VARCHAR(20)`         | `VARCHAR(255)` | Constructs a formatted string: `Nama (NIM) - Kamar NomorKamar`.          |

Never manually uppercase NIMs or lowercase/trim emails in PHP — use these UDFs (applied automatically via triggers on insert/update) instead.

### Triggers

All triggers are prefixed with `trg_` and automate background data formatting and timestamp updates:

- **Resident (`penghuni`)**: `trg_beforeInsertPenghuni` / `trg_beforeUpdatePenghuni` — normalizes `Nim`, `Email`, and `NoHP` via UDFs and sets `UpdateAt` to `NOW()`.
- **Staff (`petugas`)**: `trg_beforeInsertPetugas` / `trg_beforeUpdatePetugas` — normalizes `Email` and `NoHP` via UDFs and sets `UpdatedAt` to `NOW()`.
- **Kamar / Ruangan / Inventaris**: `trg_beforeInsertKamar` / `trg_beforeUpdateKamar` / `trg_beforeInsertRuangan` / `trg_beforeUpdateRuangan` / `trg_beforeInsertInventaris` / `trg_beforeUpdateInventaris` — sets `UpdatedAt` to `NOW()`.

Never pass `UpdateAt` / `UpdatedAt` manually in inserts or updates — these triggers handle it.

### Stored Procedures

All stored procedures are prefixed with `sp_` and camel-cased.

**Authentication & Identification**
- `sp_getPetugasByEmail(email)` — Retrieves active staff records by email.
- `sp_getPenghuniByEmail(email)` — Retrieves active resident records by email.
- `sp_checkPenghuniExist(id)` — Verifies if a resident is active and exists.

**Image & Media Retrieval**
- `sp_getFotoLaporanFromMaintenance(id)` — Retrieves report photo from maintenance ticket.
- `sp_getFotoMaintenanceFromMaintenance(id)` — Retrieves repair photo from maintenance ticket.
- `sp_getFotoPengambilanFromPengambilanPaket(id)` — Retrieves package pickup photo.

**In/Out Logs (`inoutpenghuni`)**
- `sp_createInOutRequest(penghuni_id, keperluan, waktu_keluar, waktu_masuk)` — Inserts a new exit log in pending state.
- `sp_confirmInOutExit(inout_id, waktu_keluar, petugas_id)` — Registers actual departure.
- `sp_confirmInOutEntry(inout_id, waktu_masuk)` — Registers actual return.
- `sp_countActiveInOutRequests(penghuni_id)` — Checks for active departure records.

**Kamar (Room) Management**
- `sp_getAllKamarWithOccupancy()` — Fetches all rooms and their current active occupancy count.
- `sp_getKamarDetailWithOccupancy(kamar_id)` — Fetches details and occupancy count of a single room.
- `sp_getPenghuniByKamarId(kamar_id)` — Lists all active residents currently living in the room.
- `sp_deleteKamar(kamar_id)` — Soft-deletes a room.

**Ruangan (Area) Management**
- `sp_getAllRuangan()` — Retrieves all active rooms/areas.
- `sp_getRuanganById(ruangan_id)` — Fetches details of a specific area.
- `sp_deleteRuangan(ruangan_id)` — Soft-deletes an area.

**Penghuni (Resident) Profile Management**
- `sp_getActivePenghuniForSelect()` — Retrieves all active residents formatted for drop-down selection options.
- `sp_createPenghuni(kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat)` — Inserts a new resident.
- `sp_updatePenghuniWithPassword(id, kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat)` — Updates profile details including password.
- `sp_updatePenghuniWithoutPassword(id, kamar_id, nama, nim, jk, no_hp, email, alamat)` — Updates profile details without changing password.
- `sp_deletePenghuni(id)` — Soft-deletes a resident.
- `sp_restorePenghuni(id, kamar_id, nama, nim, jk, no_hp, email, password_hash, alamat)` — Restores a soft-deleted resident.

**Paket (Package) Management**
- `sp_getPaketDetail(id)` — Retrieves details for a package.
- `sp_createPaket(petugas_id, nama_pengirim, kurir, jenis_paket, waktu_sampai, penghuni_id)` — Adds a package.
- `sp_updatePaket(id, nama_pengirim, kurir, jenis_paket, waktu_sampai, penghuni_id)` — Updates package details.
- `sp_deletePaket(id)` — Deletes a package.
- `sp_countPackagePickupByPaketId(id)` — Checks referencing package pickup records.

**Petugas (Staff) Management**
- `sp_getAllPetugas()` — Retrieves all active staff.
- `sp_findPetugasDuplicateActive(email, no_hp)` / `sp_findPetugasDuplicateDeleted(email, no_hp)` — Duplicate-contact checks used before create.
- `sp_createPetugas(nama, email, password_hash, jabatan, no_hp)` — Inserts a new staff member.
- `sp_getPetugasById(id)` — Fetches one active staff record.
- `sp_findPetugasDuplicateExcluding(id, email, no_hp)` — Duplicate-contact check used before update, excluding self.
- `sp_updatePetugasWithPassword(id, nama, email, jabatan, no_hp, password_hash)` / `sp_updatePetugasWithoutPassword(id, nama, email, jabatan, no_hp)` — Updates profile, with or without changing password.
- `sp_restorePetugas(id, nama, email, password_hash, jabatan, no_hp)` — Restores a soft-deleted staff member found via the duplicate-deleted check.
- `sp_deletePetugas(id)` — Soft-deletes a staff member.

**Ruangan (Area) Writes** (reads/delete already listed above)
- `sp_createRuangan(nama, jenis, lantai, keterangan)` — Adds an area.
- `sp_updateRuangan(id, nama, jenis, lantai, keterangan)` — Updates an area.

**Kamar (Room) Additional Management** (index/detail/delete already listed above)
- `sp_getKamarById(id)` — Fetches one room's raw row (used by edit form).
- `sp_countActivePenghuniByKamar(kamar_id)` — Current occupancy count, used for capacity validation.
- `sp_findKamarDuplicateNomor(nomor, exclude_id)` — Duplicate `NomorKamar` check; pass `exclude_id = 0` for create, or the room's own ID for edit.
- `sp_createKamar(nomor, kapasitas, lantai)` / `sp_updateKamar(id, nomor, kapasitas, lantai)` — Create/update a room.

**Inventaris (Inventory) Management**
- `sp_getAllInventarisWithLokasi()` — Lists active inventory joined with its kamar/ruangan location name.
- `sp_getActiveKamarOptions()` / `sp_getActiveRuanganOptions()` — Location picker option lists (also reused by maintenance target pickers).
- `sp_checkKamarActive(kamar_id)` / `sp_checkRuanganActive(ruangan_id)` — Location-exists validation (also reused by `checkMaintenanceTargetExists()` for the `ruangan` case).
- `sp_getInventarisById(id)` — Fetches one inventory item.
- `sp_createInventaris(ruangan_id, kamar_id, nama, jumlah, keterangan)` / `sp_updateInventaris(id, ruangan_id, kamar_id, nama, jumlah, keterangan)` — Create/update. Exactly one of `ruangan_id`/`kamar_id` is non-null.
- `sp_deleteInventaris(id)` — Soft-deletes an inventory item.

**Penghuni (Resident) Reads** (writes already listed above)
- `sp_getPenghuniIdentityRows(is_deleted, exclude_id)` — Nim/Email/NoHP rows for duplicate checks; pass `exclude_id = 0` when not excluding a row.
- `sp_getPenghuniList()` — Index page list joined with `NomorKamar`.
- `sp_getKamarForPenghuniAssignment(kamar_id)` — Room lookup used when assigning a resident to a room.
- `sp_getPenghuniRoomOccupantSummary(kamar_id, exclude_id)` — Occupant count + gender list for a room, for capacity/gender-mix validation; `exclude_id = 0` when not excluding.
- `sp_getPenghuniByIdFull(id)` — Full resident row (distinct from `sp_checkPenghuniExist`, which only returns the ID).
- `sp_getActiveKamarWithOccupancy()` — Room list with live occupancy count, used for the resident assignment dropdown.

**Pengambilanpaket (Package Pickup) Management**
- `sp_getPaketListForRole(role, user_id)` — Paket list joined with the latest pickup record; SIGAP/PENGURUS see all, PENGHUNI is scoped to `user_id`.
- `sp_getPaketWithLatestPickup(paket_id, penghuni_id)` — Single paket + latest pickup; pass `penghuni_id = 0` to skip resident scoping.
- `sp_insertPaketPickup(paket_id, penghuni_id, petugas_id, foto, waktu, status, keterangan)` / `sp_updatePaketPickup(id, penghuni_id, petugas_id, foto, waktu, status, keterangan)` — Create/update a pickup record.
- `sp_updatePaketPickupReview(id, petugas_id, status, keterangan)` — SIGAP/PENGURUS review of a pickup (e.g. marking `TERTUKAR`).

**InOut Reads** (writes already listed above)
- `sp_getInOutHistoryForPenghuni(penghuni_id)` — A resident's own in/out history.
- `sp_getPendingInOutRequests()` / `sp_getOutsideInOutRequests()` / `sp_getAllInOutLogs()` — Staff-facing queue/log views.

**Maintenance Reads & Writes** (photo lookups already listed above)
- `sp_getMaintenanceReportsForRole(role, user_id)` — Role-scoped report list (MAINTENANCE sees all; PENGHUNI scoped to own reports; other staff scoped to reports they filed with no resident attached), sorted by priority then recency.
- `sp_getMaintenanceReportById(id)` — Single report.
- `sp_getMaintenanceRooms(only_active)` / `sp_getMaintenanceInventory(only_active)` — Target picker option lists.
- `sp_checkInventarisActive(inventaris_id)` — Inventaris-target existence check (mirrors `sp_checkRuanganActive` for the `ruangan` case).
- `sp_createMaintenanceReport(...)` / `sp_updateMaintenanceReport(...)` — Create/update a report.
- `sp_claimMaintenanceReport(petugas_id, id)` — MAINTENANCE staff claims a `Diajukan` report (moves it to `Diproses`).
- `sp_checkMaintenanceTechnicianOwnership(id, petugas_id)` — Verifies the calling technician owns the in-progress report before completing it.
- `sp_completeMaintenanceReport(tanggal_selesai, keterangan, foto_maintenance, id)` — Marks a report `Selesai`.
- `sp_deleteMaintenanceReport(id)` — Soft-deletes a report.

**Dashboard Stats** (`dashboard/index.php`)
- `sp_getDashboardPengurusStats()` — Active-resident/pending-inout/pending-maintenance/pending-pickup counts in one row.
- `sp_getDashboardSigapStats()` — SIGAP pending in/out confirmations, current outside-resident count, pending package pickups, and today's package count in one row.
- `sp_getDashboardGenderStats()` — Resident gender distribution.
- `sp_getDashboardPenghuniIzinAktif(user_id)` / `sp_getDashboardPenghuniPaketSummary(user_id)` / `sp_getDashboardPenghuniMaintenanceSummary(user_id)` — Per-resident dashboard summaries.
- `sp_getDashboardMaintenanceCounts(user_id)` — Pending/ongoing/completed/emergency counts for the MAINTENANCE dashboard.
- `sp_getDashboardMyTasks(user_id)` / `sp_getDashboardEmergencyList()` — Task list + active-emergency list for the MAINTENANCE dashboard.
- `sp_getMaintenanceStatusPie()` / `sp_getMaintenanceTrendDaily(interval_days)` / `sp_getMaintenanceTrendMonthly()` — Chart data; `interval_days` is `6` for the 7-day chart and `29` for the 30-day chart.

**Report Procedures** (`dashboard/{paket,inout,maintenance}/report.php`)

Each of the three report pages has an identically-shaped set of ~9-11 procedures — export, stats, distribution(s), trend-daily, trend-monthly, top-N ranking(s), and detail — all taking a single `range_param VARCHAR(3)` (`'7d'|'30d'|'6m'|'all'`) and applying the date filter server-side via `WHERE (range_param = '7d' AND ... ) OR (range_param = '30d' AND ...) OR ... OR range_param = 'all'`. `$range` is validated against a 4-value whitelist in PHP before being passed, so this is safe.

- **Paket report**: `sp_getPaketReportExport`, `sp_getPaketReportStats`, `sp_getPaketReportStatusDist`, `sp_getPaketReportTrendDaily`, `sp_getPaketReportTrendMonthly`, `sp_getPaketReportTipeDist`, `sp_getPaketReportTopKurir`, `sp_getPaketReportJamSibuk`, `sp_getPaketReportTopPenghuni`, `sp_getPaketReportPetugasRanking`, `sp_getPaketReportDetail`.
- **InOut report**: `sp_getInOutReportExport`, `sp_getInOutReportStats`, `sp_getInOutReportStatusDist`, `sp_getInOutReportTrendDaily`, `sp_getInOutReportTrendMonthly`, `sp_getInOutReportPeakHour`, `sp_getInOutReportTopPenghuni`, `sp_getInOutReportGenderDist`, `sp_getInOutReportTopKeperluan`, `sp_getInOutReportPetugasRanking`, `sp_getInOutReportDetail`.
- **Maintenance report**: `sp_getMaintenanceReportExport`, `sp_getMaintenanceReportStats`, `sp_getMaintenanceReportPriorityDist`, `sp_getMaintenanceReportTrendDaily`, `sp_getMaintenanceReportTrendMonthly`, `sp_getMaintenanceReportTopRuangan`, `sp_getMaintenanceReportStackedStatus`, `sp_getMaintenanceReportPetugasRanking`, `sp_getMaintenanceReportDetail`.

### Implementation Reference

See the migration scripts for DDL schemas and implementation detail verification:
- [20260630_stored_procedures_functions_triggers.sql](file:///C:/xampp/htdocs/doremi-app/database/migrations/20260630_stored_procedures_functions_triggers.sql) — original UDFs/triggers + first batch of procedures (auth, photos, in/out writes, kamar/ruangan reads+delete, penghuni writes, paket master writes).
- [20260709_stored_procedures_batch2.sql](file:///D:/utils/laragon/www/doremi-app/database/migrations/20260709_stored_procedures_batch2.sql) — second batch, completing the migration: petugas, ruangan/kamar writes, inventaris, penghuni reads, pengambilanpaket, in/out reads, maintenance reads+writes, dashboard, and all three report pages.

---

## Coding Conventions

- **Never write raw `SELECT`/`INSERT`/`UPDATE`/`DELETE` queries** for entities that already have a stored procedure — call the procedure instead via prepared statements (e.g. `CALL sp_someProcedure(?, ?)`).
- If new database logic is needed, implement it as a new stored procedure following the `sp_` naming convention above, rather than inlining SQL in PHP.
- Prefer the existing helper layer: add fetch/action functions to `database/<part>.php` and call `dbFetchAll`, `dbFetchOne`, `dbFetchValue`, or `dbExecute` from `database/query.php`.
- Keep page controllers thin: require helpers, collect input, call validation, call domain functions, then render or redirect.
- Use camelCase for new helper function names.
- Put non-database generic helpers in `utils/`, not `database/`.
- Put validation logic in `auth/validation.php` or `dashboard/<part>/validation.php`, matching the app area.
- Always use **prepared statements** (`mysqli_prepare` / `mysqli_stmt_bind_param`) when calling stored procedures — never interpolate user input into the `CALL` statement.
- Always filter soft-deleted records: `WHERE IsDeleted = 0` (already handled inside relevant stored procedures — don't re-filter redundantly unless the procedure doesn't do it).
- **Never manually set or pass `UpdateAt` / `UpdatedAt`** — the `trg_before...` triggers handle this automatically.
- **Use UDFs for sanitization/formatting** (`udf_normalizeNim`, `udf_normalizeEmail`, `udf_normalizePhone`, `udf_formatPenghuniLabel`) instead of manual string manipulation in PHP — most of these already run automatically via triggers on insert/update.
- Redirect after every POST with `header("Location: ...")` followed by `exit;`.
- Validate all input with `Respect\Validation` before touching the database.
- Keep `v::alnum()` off email fields — emails contain `@` and `.` which fail alnum checks.
- Pass a `(object)` cast array to `v::attribute()` schemas, not a raw string variable.
- For report-page date ranges, whitelist `$range` against `['7d', '30d', '6m', 'all']` in PHP (as the existing report pages do) and pass it straight through as a stored-procedure `VARCHAR(3)` param — implement the date filter as `WHERE (range_param = '7d' AND ...) OR ... OR range_param = 'all'` inside the procedure, not as an interpolated SQL fragment built in PHP.
- For optional "exclude this row" ID parameters (duplicate checks, occupant-summary checks), use a `0` sentinel for "no exclusion" (e.g. `sp_findKamarDuplicateNomor(nomor, exclude_id)` with `exclude_id = 0` on create) rather than a nullable/optional SQL fragment, since all primary keys are auto-increment `> 0`.
