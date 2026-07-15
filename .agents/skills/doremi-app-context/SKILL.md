---
name: doremi-app-context
description: Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.
alwaysApply: true
---

# Doremi â€” Dormitory Management App

## Project Overview

Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.

As of **June 30, 2026** (batch 1) and **July 9, 2026** (batch 2, completing the migration), the database layer has been fully migrated away from inline queries: **every** CRUD and reporting query in the app now goes through **Stored Procedures**, formatting/sanitization is handled by **User-Defined Functions (UDFs)**, and field constraints/auto-timestamps are automated via **Triggers**. There is no raw `SELECT`/`INSERT`/`UPDATE`/`DELETE` left in any request-flow PHP file (the only exception is `database/dump_to_md.php`, a dev-only CLI schema-dump tool outside the app's request flow). See [Database Layer](#database-layer-stored-procedures-udfs--triggers) below before writing any new database code.

---

## Tech Stack

- **Backend**: PHP (native, no framework)
- **Frontend**: Tailwind CSS 4.0 with `tw` prefix + Bootstrap (for components like forms, tables, modals) + Alpine.js when reactivity is needed
- **Database**: MySQL 8.0, accessed via `mysqli_*` functions â€” **but only by calling stored procedures**, not raw SQL (see below)
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
- `database/ruangan.php` - `createRuangan`, `updateRuangan`; reads and deletion use parameterized SQL.
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

**All CSS edits must be made in `index.css` only.** This file is the Tailwind CSS v4 source and is compiled by the Tailwind CLI into `css/main.css`. Never edit `css/main.css` directly â€” it is a generated file and any manual changes will be overwritten on the next build.

- Source file: `index.css` (project root)
- Compiled output: `css/main.css` (do not touch)
- Tailwind config is embedded in `index.css` via `@theme { ... }` â€” add new design tokens there
- All Tailwind utilities are prefixed with `tw:` (e.g. `tw:flex`, `tw:text-sm`)
- The `important` modifier is active globally, so all `tw:*` utilities emit `!important`
- Bootstrap CSS is loaded separately from CDN (`head.php`) â€” override Bootstrap variables via `:root { --bs-... }` inside `index.css`
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

- Page backgrounds â†’ `--color-background`
- Primary actions/buttons â†’ `--color-primary`
- Hover/interactive states â†’ `--color-secondary` or `--color-tertiary`
- Subtle highlights/badges â†’ `--color-accent`

---

## Authentication

Call `session_start()` at the top of every page.

Current login implementation uses helper functions: `authAttemptPasswordLogin()` for password login and `authAttemptEmailLogin()` for Google login. These helpers call parameterized lookups in `database/auth.php`.

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
| `PENGHUNI`    | hardcoded string  | Resident â€” no column in DB, assigned in PHP |

**Login flow**: check `petugas` first, then `penghuni`; if neither matches, return an error.

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
| `UpdatedAt`   | datetime                                          | DEFAULT CURRENT_TIMESTAMP; auto-set by `trg_beforeInsertPetugas`/`trg_beforeUpdatePetugas` â€” never set manually |
| `IsDeleted`   | tinyint(1)                                        | Soft delete, default 0     |

### `penghuni`

| Column         | Type                | Notes                                                                   |
| -------------- | ------------------- | ------------------------------------------------------------------------ |
| `PenghuniID`   | int PK AI           |                                                                            |
| `KamarID`      | int FK â†’ kamar      |                                                                            |
| `NamaPenghuni` | varchar(100)        |                                                                            |
| `Nim`          | varchar(10)         | Student ID â€” normalized by `udf_normalizeNim` via insert/update triggers |
| `JenisKelamin` | enum('L','P')       |                                                                            |
| `NoHP`         | varchar(20)         | Normalized by `udf_normalizePhone` via triggers                          |
| `Email`        | varchar(100) UNIQUE | Normalized by `udf_normalizeEmail` via triggers                          |
| `Password`     | varchar(100)        | bcrypt                                                                    |
| `Alamat`       | text                |                                                                            |
| `IsActive`     | tinyint(1)          | default 1                                                                 |
| `UpdateAt`     | datetime            | Auto-set by `trg_beforeInsertPenghuni`/`trg_beforeUpdatePenghuni` â€” never set manually |
| `IsDeleted`    | tinyint(1)          | default 0                                                                 |

### `kamar`

| Column              | Type        | Notes                                                                |
| ------------------- | ----------- | --------------------------------------------------------------------- |
| `KamarID`           | int PK AI   |                                                                         |
| `NomorKamar`        | varchar(2)  | e.g. `"1A"` â€” lantai digit + one letter, built by `kamar_build_nomor()` in `dashboard/kamar/helpers.php` |
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
| `RuanganID`    | int FK â†’ ruangan |                                                                            |
| `KamarID`      | int FK â†’ kamar   |                                                                            |
| `NamaBarang`   | varchar(100)     |                                                                            |
| `Jumlah`       | int              |                                                                            |
| `Keterangan`   | text             |                                                                            |
| `UpdatedAt`    | datetime         | Auto-set by `trg_beforeInsertInventaris`/`trg_beforeUpdateInventaris`     |
| `IsDeleted`    | tinyint(1)       |                                                                            |

### `paket`

| Column         | Type              | Notes                  |
| -------------- | ------------------ | ----------------------- |
| `PaketID`      | int PK AI          |                          |
| `PetugasID`    | int FK â†’ petugas   | Staff who received it   |
| `PenghuniID`   | int FK â†’ penghuni  | Intended recipient      |
| `NamaPengirim` | varchar(100)       |                          |
| `Kurir`        | varchar(50)        | Courier name            |
| `WaktuSampai`  | datetime           |                          |

### `pengambilanpaket`

| Column               | Type                                   | Notes |
| -------------------- | --------------------------------------- | ----- |
| `PengambilanPaketID` | int PK AI                               |       |
| `PaketID`            | int FK â†’ paket                          |       |
| `PenghuniID`         | int FK â†’ penghuni                       |       |
| `PetugasID`          | int FK â†’ petugas                        |       |
| `FotoPengambilan`    | longtext                                |       |
| `WaktuPengambilan`   | datetime                                |       |
| `Status`             | enum('Belum Diambil','Sudah Diambil')   |       |
| `Keterangan`         | text                                     |       |

### `inoutpenghuni`

| Column        | Type                               | Notes           |
| ------------- | ----------------------------------- | ----------------- |
| `InOutID`     | int PK AI                          |                    |
| `PenghuniID`  | int FK â†’ penghuni                  |                    |
| `PetugasID`   | int FK â†’ petugas                   | Guard on duty      |
| `WaktuKeluar` | datetime                           | Nullable           |
| `WaktuMasuk`  | datetime                           |                    |
| `Keperluan`   | varchar(100)                       | Purpose of exit    |
| `Status`      | enum('Masuk','Keluar','Pending')   |                    |

### `maintenance`

| Column              | Type                                              | Notes          |
| ------------------- | --------------------------------------------------- | -------------- |
| `MaintenanceID`     | int PK AI                                            |                |
| `PenghuniID`        | int FK â†’ penghuni                                    | Reporter       |
| `PetugasID`         | int FK â†’ petugas                                     | Assigned staff |
| `RuanganID`         | int FK â†’ ruangan                                     |                |
| `InventarisID`      | int FK â†’ inventaris                                  |                |
| `TanggalLapor`      | datetime                                             |                |
| `JenisLaporan`      | enum('Kerusakan Ringan','Kerusakan Sedang','Kerusakan Darurat / Berat') | Priority scale, not a category â€” used to sort/prioritize reports |
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

Never manually uppercase NIMs or lowercase/trim emails in PHP â€” use these UDFs (applied automatically via triggers on insert/update) instead.

### Triggers

All triggers are prefixed with `trg_` and automate background data formatting and timestamp updates:

- **Resident (`penghuni`)**: `trg_beforeInsertPenghuni` / `trg_beforeUpdatePenghuni` â€” normalizes `Nim`, `Email`, and `NoHP` via UDFs and sets `UpdateAt` to `NOW()`.
- **Staff (`petugas`)**: `trg_beforeInsertPetugas` / `trg_beforeUpdatePetugas` â€” normalizes `Email` and `NoHP` via UDFs and sets `UpdatedAt` to `NOW()`.
- **Kamar / Ruangan / Inventaris**: `trg_beforeInsertKamar` / `trg_beforeUpdateKamar` / `trg_beforeInsertRuangan` / `trg_beforeUpdateRuangan` / `trg_beforeInsertInventaris` / `trg_beforeUpdateInventaris` â€” sets `UpdatedAt` to `NOW()`.

Never pass `UpdateAt` / `UpdatedAt` manually in inserts or updates â€” these triggers handle it.

### Stored Procedures

Only these workflow procedures remain in production:

- `sp_createInOutRequest` — creates a resident in/out request in `Pending` status.
- `sp_confirmInOutExit` — confirms departure, records the actual exit time, and assigns the staff member.
- `sp_confirmInOutEntry` — confirms return and records the actual entry time.
- `sp_insertPaketPickup` — creates a package-pickup record with its proof photo and status.
- `sp_updatePaketPickup` — updates an existing package-pickup record.
- `sp_updatePaketPickupReview` — records staff review status and notes for a package pickup.
- `sp_createMaintenanceReport` — creates a maintenance report with its reporter, target, priority, and photo.
- `sp_updateMaintenanceReport` — updates a maintenance report's target, priority, description, or photo.
- `sp_claimMaintenanceReport` — assigns an eligible maintenance technician to an open report and moves it to `Diproses`.
- `sp_completeMaintenanceReport` — marks a claimed report complete with completion time, notes, and repair photo.

### Implementation Reference
See the migration scripts for DDL schemas and implementation detail verification:
- [20260630_stored_procedures_functions_triggers.sql](file:///C:/xampp/htdocs/doremi-app/database/migrations/20260630_stored_procedures_functions_triggers.sql) â€” original UDFs/triggers + first batch of procedures (auth, photos, in/out writes, kamar/ruangan reads+delete, penghuni writes, paket master writes).
- [20260709_stored_procedures_batch2.sql](file:///D:/utils/laragon/www/doremi-app/database/migrations/20260709_stored_procedures_batch2.sql) â€” second batch, completing the migration: petugas, ruangan/kamar writes, inventaris, penghuni reads, pengambilanpaket, in/out reads, maintenance reads+writes, dashboard, and all three report pages.

---

## Coding Conventions

- **Never write raw `SELECT`/`INSERT`/`UPDATE`/`DELETE` queries** for entities that already have a stored procedure â€” call the procedure instead via prepared statements (e.g. `CALL sp_someProcedure(?, ?)`).
- If new database logic is needed, implement it as a new stored procedure following the `sp_` naming convention above, rather than inlining SQL in PHP.
- Prefer the existing helper layer: add fetch/action functions to `database/<part>.php` and call `dbFetchAll`, `dbFetchOne`, `dbFetchValue`, or `dbExecute` from `database/query.php`.
- Keep page controllers thin: require helpers, collect input, call validation, call domain functions, then render or redirect.
- Use camelCase for new helper function names.
- Put non-database generic helpers in `utils/`, not `database/`.
- Put validation logic in `auth/validation.php` or `dashboard/<part>/validation.php`, matching the app area.
- Always use **prepared statements** (`mysqli_prepare` / `mysqli_stmt_bind_param`) when calling stored procedures â€” never interpolate user input into the `CALL` statement.
- Always filter soft-deleted records: `WHERE IsDeleted = 0` (already handled inside relevant stored procedures â€” don't re-filter redundantly unless the procedure doesn't do it).
- **Never manually set or pass `UpdateAt` / `UpdatedAt`** â€” the `trg_before...` triggers handle this automatically.
- **Use UDFs for sanitization/formatting** (`udf_normalizeNim`, `udf_normalizeEmail`, `udf_normalizePhone`, `udf_formatPenghuniLabel`) instead of manual string manipulation in PHP â€” most of these already run automatically via triggers on insert/update.
- Redirect after every POST with `header("Location: ...")` followed by `exit;`.
- Validate all input with `Respect\Validation` before touching the database.
- Keep `v::alnum()` off email fields â€” emails contain `@` and `.` which fail alnum checks.
- Pass a `(object)` cast array to `v::attribute()` schemas, not a raw string variable.
- For report-page date ranges, whitelist `$range` against `['7d', '30d', '6m', 'all']` in PHP (as the existing report pages do) and pass it straight through as a stored-procedure `VARCHAR(3)` param â€” implement the date filter as `WHERE (range_param = '7d' AND ...) OR ... OR range_param = 'all'` inside the procedure, not as an interpolated SQL fragment built in PHP.
- For optional "exclude this row" ID parameters (duplicate checks, occupant-summary checks), use a `0` sentinel for "no exclusion" (e.g. `sp_findKamarDuplicateNomor(nomor, exclude_id)` with `exclude_id = 0` on create) rather than a nullable/optional SQL fragment, since all primary keys are auto-increment `> 0`.
