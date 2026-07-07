---
name: doremi-app-context
description: Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.
alwaysApply: true
---

# Doremi — Dormitory Management App

## Project Overview

Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.

As of **June 30, 2026**, the database layer has been migrated away from inline queries: all CRUD operations now go through **Stored Procedures**, formatting/sanitization is handled by **User-Defined Functions (UDFs)**, and field constraints/auto-timestamps are automated via **Triggers**. See [Database Layer](#database-layer-stored-procedures-udfs--triggers) below before writing any new database code.

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
- `database/paket.php` - package list/detail, package CRUD procedure calls, pickup/review operations, package photo fetching.
- `database/inout.php` - in/out list fetches and stored-procedure actions.
- `database/maintenance.php` - maintenance list/detail fetches, room/inventory option fetches, ownership checks, create/update/claim/complete/delete actions, photo fetching.
- `database/penghuni.php` - resident fetches, room occupancy fetches, duplicate/assignment support queries, create/update/restore procedure calls.
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
| `NomorKamar`        | varchar(20) |                                                                         |
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
| `JenisLaporan`      | enum('Kerusakan','Kebersihan','Keluhan')             |                |
| `Deskripsi`         | text                                                  |                |
| `StatusMaintenance` | enum('Diajukan','Diproses','Selesai','Ditolak')      |                |
| `TanggalSelesai`    | datetime NULL                                        |                |
| `Keterangan`        | text NULL                                            |                |
| `FotoLaporan`       | longtext                                              |                |
| `FotoMaintenance`   | longtext                                              |                |

---

## Database Layer: Stored Procedures, UDFs & Triggers

The database layer migration (June 30, 2026) replaced inline queries with three layers: stored procedures for CRUD, UDFs for sanitization/formatting, and triggers for automatic constraints/timestamps. New database logic should be implemented at this layer, not as raw inline SQL.

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

### Implementation Reference

See the migration script: [20260630_stored_procedures_functions_triggers.sql](file:///C:/xampp/htdocs/doremi-app/database/migrations/20260630_stored_procedures_functions_triggers.sql) for DDL schemas and implementation detail verification.

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
