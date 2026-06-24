---
name: doremi-app-context
description: Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.
alwaysApply: true
---

# Doremi — Dormitory Management App

## Project Overview

Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.

---

## Tech Stack

- **Backend**: PHP (native, no framework)
- **Frontend**: Tailwind CSS 4.0 with tw prefix (look at the files i've worked on to be sure) + Bootstrap (for components like form and tables or even modal) + Alpine.Js if i/you want to have reactivity
- **Database**: MySQL 8.0, accessed via `mysqli_*` functions
- **DB connection**: `$db` variable from `require_once 'db.php'`
- **Validation**: `Respect\Validation` (`use Respect\Validation\Validator as v;`)
- **Passwords**: `password_hash($pass, PASSWORD_BCRYPT)` / `password_verify()`
- **Auth**: PHP Sessions

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

| Session Key             | Value                                            |
| ----------------------- | ------------------------------------------------ |
| `$_SESSION['userId']`   | PetugasID (staff roles) or PenghuniID (resident) |
| `$_SESSION['userName']` | NamaPetugas or NamaPenghuni                      |
| `$_SESSION['userRole']` | One of the 5 roles below                         |

### Roles

| Role          | Source            | Notes                                       |
| ------------- | ----------------- | ------------------------------------------- |
| `PENGURUS`    | `petugas.Jabatan` | Admin/manager                               |
| `SIGAP`       | `petugas.Jabatan` | Security staff                              |
| `VIRTUS`      | `petugas.Jabatan` | General staff                               |
| `MAINTENANCE` | `petugas.Jabatan` | Maintenance staff                           |
| `PENGHUNI`    | hardcoded string  | Resident — no column in DB, assigned in PHP |

**Login flow**: check `petugas` table first → if email not found, check `penghuni` → if neither matches, return error.

---

## Database Schema

### `petugas`

| Column        | Type                                            | Notes                     |
| ------------- | ----------------------------------------------- | ------------------------- |
| `PetugasID`   | int PK AI                                       |                           |
| `NamaPetugas` | varchar(100)                                    |                           |
| `Email`       | varchar(100) UNIQUE                             |                           |
| `Password`    | text                                            | bcrypt                    |
| `Jabatan`     | enum('PENGURUS','SIGAP','VIRTUS','MAINTENANCE') |                           |
| `NoHP`        | varchar(20)                                     |                           |
| `UpdatedAt`   | datetime                                        | DEFAULT CURRENT_TIMESTAMP |
| `IsDeleted`   | tinyint(1)                                      | Soft delete, default 0    |

### `penghuni`

| Column         | Type                | Notes      |
| -------------- | ------------------- | ---------- |
| `PenghuniID`   | int PK AI           |            |
| `KamarID`      | int FK → kamar      |            |
| `NamaPenghuni` | varchar(100)        |            |
| `Nim`          | varchar(10)         | Student ID |
| `JenisKelamin` | enum('L','P')       |            |
| `NoHP`         | varchar(20)         |            |
| `Email`        | varchar(100) UNIQUE |            |
| `Password`     | varchar(100)        | bcrypt     |
| `Alamat`       | text                |            |
| `IsActive`     | tinyint(1)          | default 1  |
| `UpdateAt`     | datetime            |            |
| `IsDeleted`    | tinyint(1)          | default 0  |

### `kamar`

| Column              | Type        | Notes         |
| ------------------- | ----------- | ------------- |
| `KamarID`           | int PK AI   |               |
| `NomorKamar`        | varchar(20) |               |
| `KapasitasPenghuni` | int         | Room capacity |
| `UpdatedAt`         | datetime    |               |
| `IsDeleted`         | tinyint(1)  |               |

### `ruangan`

| Column         | Type                              | Notes |
| -------------- | --------------------------------- | ----- |
| `RuanganID`    | int PK AI                         |       |
| `NamaRuangan`  | varchar(100)                      |       |
| `JenisRuangan` | varchar(50)                       |       |
| `Keterangan`   | text                              |       |
| `UpdatedAt`    | datetime                          |       |
| `IsDeleted`    | tinyint(1)                        |       |
| `Lantai`       | enum('1','2','3','4','5','6','7') |       |

### `inventaris`

| Column         | Type             | Notes |
| -------------- | ---------------- | ----- |
| `InventarisID` | int PK AI        |       |
| `RuanganID`    | int FK → ruangan |       |
| `KamarID`      | int FK → kamar   |       |
| `NamaBarang`   | varchar(100)     |       |
| `Jumlah`       | int              |       |
| `Keterangan`   | text             |       |
| `UpdatedAt`    | datetime         |       |
| `IsDeleted`    | tinyint(1)       |       |

### `paket`

| Column         | Type              | Notes                 |
| -------------- | ----------------- | --------------------- |
| `PaketID`      | int PK AI         |                       |
| `PetugasID`    | int FK → petugas  | Staff who received it |
| `PenghuniID`   | int FK → penghuni | Intended recipient    |
| `NamaPengirim` | varchar(100)      |                       |
| `Kurir`        | varchar(50)       | Courier name          |
| `WaktuSampai`  | datetime          |                       |

### `pengambilanpaket`

| Column               | Type                                  | Notes |
| -------------------- | ------------------------------------- | ----- |
| `PengambilanPaketID` | int PK AI                             |       |
| `PaketID`            | int FK → paket                        |       |
| `PenghuniID`         | int FK → penghuni                     |       |
| `PetugasID`          | int FK → petugas                      |       |
| `FotoPengambilan`    | longtext                              |       |
| `WaktuPengambilan`   | datetime                              |       |
| `Status`             | enum('Belum Diambil','Sudah Diambil') |       |
| `Keterangan`         | text                                  |       |

### `inoutpenghuni`

| Column        | Type                             | Notes           |
| ------------- | -------------------------------- | --------------- |
| `InOutID`     | int PK AI                        |                 |
| `PenghuniID`  | int FK → penghuni                |                 |
| `PetugasID`   | int FK → petugas                 | Guard on duty   |
| `WaktuKeluar` | datetime                         | Nullable        |
| `WaktuMasuk`  | datetime                         |                 |
| `Keperluan`   | varchar(100)                     | Purpose of exit |
| `Status`      | enum('Masuk','Keluar','Pending') |                 |

### `maintenance`

| Column              | Type                                            | Notes          |
| ------------------- | ----------------------------------------------- | -------------- |
| `MaintenanceID`     | int PK AI                                       |                |
| `PenghuniID`        | int FK → penghuni                               | Reporter       |
| `PetugasID`         | int FK → petugas                                | Assigned staff |
| `RuanganID`         | int FK → ruangan                                |                |
| `InventarisID`      | int FK → inventaris                             |                |
| `TanggalLapor`      | datetime                                        |                |
| `JenisLaporan`      | enum('Kerusakan','Kebersihan','Keluhan')        |                |
| `Deskripsi`         | text                                            |                |
| `StatusMaintenance` | enum('Diajukan','Diproses','Selesai','Ditolak') |                |
| `TanggalSelesai`    | datetime NULL                                   |                |
| `Keterangan`        | text NULL                                       |                |
| `FotoLaporan`       | longtext                                        |                |
| `FotoMaintenance`   | longtext                                        |                |

---

## Coding Conventions

- Always use **prepared statements** — never interpolate user input into queries.
- Always filter soft-deleted records: `WHERE IsDeleted = 0`
- Redirect after every POST: `header("Location: ...")` then `exit;`
- Validate all input with `Respect\Validation` before touching the DB.
