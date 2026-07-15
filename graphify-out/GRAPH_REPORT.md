# Graph Report - doremi-app  (2026-07-15)

## Corpus Check
- 115 files · ~1,254,750 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 584 nodes · 857 edges · 134 communities (88 shown, 46 thin omitted)
- Extraction: 80% EXTRACTED · 20% INFERRED · 0% AMBIGUOUS · INFERRED: 174 edges (avg confidence: 0.75)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `e9e91553`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- [[_COMMUNITY_DataTables JS Library|DataTables JS Library]]
- [[_COMMUNITY_InOut Request Management|In/Out Request Management]]
- [[_COMMUNITY_Database & Auth Helpers|Database & Auth Helpers]]
- [[_COMMUNITY_Resident (Penghuni) Helpers|Resident (Penghuni) Helpers]]
- [[_COMMUNITY_Maintenance Report Validation|Maintenance Report Validation]]
- [[_COMMUNITY_Package (Paket) Helpers|Package (Paket) Helpers]]
- [[_COMMUNITY_Authentication Flow|Authentication Flow]]
- [[_COMMUNITY_InOut Input & Validation|In/Out Input & Validation]]
- [[_COMMUNITY_Composer Project Config|Composer Project Config]]
- [[_COMMUNITY_Coding Guidelines|Coding Guidelines]]
- [[_COMMUNITY_Room (Kamar) Helpers|Room (Kamar) Helpers]]
- [[_COMMUNITY_Maintenance Helpers|Maintenance Helpers]]
- [[_COMMUNITY_Room Facility Images|Room Facility Images]]
- [[_COMMUNITY_Dining Facility Images|Dining Facility Images]]
- [[_COMMUNITY_Recreation Facility Images|Recreation Facility Images]]
- [[_COMMUNITY_Dormitory Staff & Org|Dormitory Staff & Org]]
- [[_COMMUNITY_Outdoor Facilities Images|Outdoor Facilities Images]]
- [[_COMMUNITY_Node.js  Tailwind Config|Node.js / Tailwind Config]]
- [[_COMMUNITY_Error UI Feedback|Error UI Feedback]]
- [[_COMMUNITY_Doremi Brand Identity|Doremi Brand Identity]]
- [[_COMMUNITY_Market & Inventory|Market & Inventory]]
- [[_COMMUNITY_Bathroom Facility Images|Bathroom Facility Images]]
- [[_COMMUNITY_Dev Start Scripts|Dev Start Scripts]]
- [[_COMMUNITY_Auditorium Event Photo|Auditorium Event Photo]]
- [[_COMMUNITY_Basketball Court Photo|Basketball Court Photo]]
- [[_COMMUNITY_Success UI Feedback|Success UI Feedback]]
- [[_COMMUNITY_dbFetchAll|dbFetchAll]]
- [[_COMMUNITY_Database Schema|Database Schema]]
- [[_COMMUNITY_Database Schema|Database Schema]]
- [[_COMMUNITY_Database Dump — `astrador_doremiapp`|Database Dump — `astrador_doremiapp`]]
- [[_COMMUNITY_Doremi App — Agent Instructions|Doremi App — Agent Instructions]]
- [[_COMMUNITY_Graphify — Codebase Knowledge Graph|Graphify — Codebase Knowledge Graph]]
- [[_COMMUNITY_Karpathy behavioral guidelines|Karpathy behavioral guidelines]]
- [[_COMMUNITY_Karpathy behavioral guidelines|Karpathy behavioral guidelines]]
- [[_COMMUNITY_CLAUDE|CLAUDE.md]]
- [[_COMMUNITY_Goal-Driven Execution Principle|Goal-Driven Execution Principle]]
- [[_COMMUNITY_Simplicity First Principle|Simplicity First Principle]]
- [[_COMMUNITY_Surgical Changes Principle|Surgical Changes Principle]]
- [[_COMMUNITY_Think Before Coding Principle|Think Before Coding Principle]]
- [[_COMMUNITY_User Roles (PENGURUSSIGAPVIRTUSMAINTENANCEPENGHUNI)|User Roles (PENGURUS/SIGAP/VIRTUS/MAINTENANCE/PENGHUNI)]]
- [[_COMMUNITY_databaseauth.php|database/auth.php]]
- [[_COMMUNITY_databaseinout.php|database/inout.php]]
- [[_COMMUNITY_databasemaintenance.php|database/maintenance.php]]
- [[_COMMUNITY_databasepaket.php|database/paket.php]]
- [[_COMMUNITY_databasepenghuni.php|database/penghuni.php]]
- [[_COMMUNITY_databasephotos.php|database/photos.php]]
- [[_COMMUNITY_databasequery.php Shared Helpers|database/query.php Shared Helpers]]
- [[_COMMUNITY_Doremi App Context Skill (agents)|Doremi App Context Skill (agents)]]
- [[_COMMUNITY_Stored Procedures Layer|Stored Procedures Layer]]
- [[_COMMUNITY_DB Table inoutpenghuni|DB Table: inoutpenghuni]]
- [[_COMMUNITY_DB Table inventaris|DB Table: inventaris]]
- [[_COMMUNITY_DB Table kamar|DB Table: kamar]]
- [[_COMMUNITY_DB Table maintenance|DB Table: maintenance]]
- [[_COMMUNITY_DB Table paket|DB Table: paket]]
- [[_COMMUNITY_DB Table pengambilanpaket|DB Table: pengambilanpaket]]
- [[_COMMUNITY_DB Table penghuni|DB Table: penghuni]]
- [[_COMMUNITY_DB Table petugas|DB Table: petugas]]
- [[_COMMUNITY_DB Table ruangan|DB Table: ruangan]]
- [[_COMMUNITY_Doremi Tech Stack|Doremi Tech Stack]]
- [[_COMMUNITY_Database Triggers|Database Triggers]]
- [[_COMMUNITY_User-Defined Functions (UDFs)|User-Defined Functions (UDFs)]]
- [[_COMMUNITY_Coding Guideline Skill (claude)|Coding Guideline Skill (claude)]]
- [[_COMMUNITY_Doremi App Context Skill (claude)|Doremi App Context Skill (claude)]]
- [[_COMMUNITY_Doremi App Database Dump|Doremi App Database Dump]]
- [[_COMMUNITY_DB Data inoutpenghuni rows|DB Data: inoutpenghuni rows]]
- [[_COMMUNITY_DB Data inventaris rows|DB Data: inventaris rows]]
- [[_COMMUNITY_DB Data kamar rows|DB Data: kamar rows]]
- [[_COMMUNITY_DB Data maintenance rows|DB Data: maintenance rows]]
- [[_COMMUNITY_DB Data paket rows|DB Data: paket rows]]
- [[_COMMUNITY_DB Data pengambilanpaket rows|DB Data: pengambilanpaket rows]]
- [[_COMMUNITY_DB Data penghuni rows|DB Data: penghuni rows]]
- [[_COMMUNITY_DB Data petugas rows|DB Data: petugas rows]]
- [[_COMMUNITY_DB Data ruangan rows|DB Data: ruangan rows]]

## God Nodes (most connected - your core abstractions)
1. `dbFetchAll()` - 58 edges
2. `dbExecute()` - 33 edges
3. `dbFetchOne()` - 31 edges
4. `n()` - 19 edges
5. `se()` - 14 edges
6. `he()` - 13 edges
7. `x()` - 12 edges
8. `validatePenghuniCommonInput()` - 11 edges
9. `Database Dump — `astrador_doremiapp`` - 11 edges
10. `ne()` - 10 edges

## Surprising Connections (you probably didn't know these)
- `validatePenghuniInputSchema()` --calls--> `firstFieldError()`  [INFERRED]
  dashboard/penghuni/validation.php → utils/validation_helpers.php
- `authAttemptPasswordLogin()` --calls--> `findAuthUserByEmail()`  [INFERRED]
  auth/helpers.php → database/auth.php
- `authAttemptEmailLogin()` --calls--> `findAuthUserByEmail()`  [INFERRED]
  auth/helpers.php → database/auth.php
- `validateInOutRequestInput()` --calls--> `textLength()`  [INFERRED]
  dashboard/inout/validation.php → utils/format.php
- `validateMaintenanceReportInput()` --calls--> `checkMaintenanceTargetExists()`  [INFERRED]
  dashboard/maintenance/validation.php → database/maintenance.php

## Import Cycles
- None detected.

## Communities (134 total, 46 thin omitted)

### Community 0 - "DataTables JS Library"
Cohesion: 0.08
Nodes (60): A(), ae(), at(), B(), be(), c(), ce(), ct() (+52 more)

### Community 1 - "In/Out Request Management"
Cohesion: 0.08
Nodes (37): paket_is_valid_length(), mysqli, validatePaketInput(), checkKamarActive(), checkRuanganActive(), createInventaris(), deleteInventaris(), fetchActiveKamarOptions() (+29 more)

### Community 3 - "Resident (Penghuni) Helpers"
Cohesion: 0.12
Nodes (30): penghuni_allowed_floors(), penghuni_duplicate_identity_message(), penghuni_find_identity_matches(), penghuni_gender_label(), penghuni_is_valid_nim(), penghuni_is_valid_phone(), penghuni_nim_max_length(), penghuni_nim_min_length() (+22 more)

### Community 4 - "Maintenance Report Validation"
Cohesion: 0.08
Nodes (40): confirmInOutEntry(), confirmInOutExit(), countActiveInOutRequests(), createInOutRequest(), fetchAllInOutLogs(), fetchInOutHistoryForPenghuni(), fetchOutsideInOutRequests(), fetchPendingInOutRequests() (+32 more)

### Community 5 - "Package (Paket) Helpers"
Cohesion: 0.14
Nodes (9): paket_allowed_types(), paket_cleanup_legacy_photo(), paket_normalize_datetime(), paket_normalize_type(), paket_redirect(), paket_store_photo(), paket_type_badge_class(), paket_type_label() (+1 more)

### Community 6 - "Authentication Flow"
Cohesion: 0.09
Nodes (17): authAttemptEmailLogin(), authAttemptPasswordLogin(), authRedirectToDashboard(), authRedirectToLoginError(), mysqli, csrf_field(), csrf_token(), csrf_validate() (+9 more)

### Community 7 - "In/Out Input & Validation"
Cohesion: 0.22
Nodes (4): validateInOutRequestInput(), formatDateTime(), normalizeDateTimeInputValue(), textLength()

### Community 8 - "Composer Project Config"
Cohesion: 0.20
Nodes (9): authors, description, name, require, fpdf/fpdf, google/apiclient, respect/validation, vlucas/phpdotenv (+1 more)

### Community 10 - "Room (Kamar) Helpers"
Cohesion: 0.60
Nodes (4): kamar_build_nomor(), kamar_extract_bagian(), kamar_has_lantai_prefix(), kamar_normalize_segment()

### Community 11 - "Maintenance Helpers"
Cohesion: 0.22
Nodes (4): mysqli, validateMaintenanceReportInput(), checkMaintenanceTargetExists(), firstFieldError()

### Community 12 - "Room Facility Images"
Cohesion: 0.50
Nodes (5): Bunk Bed, Dormitory Room, Room Management Feature, Study Desk and Chair, Kamar (Room) Photo

### Community 13 - "Dining Facility Images"
Cohesion: 0.60
Nodes (5): Canteen / Dining Area, Dormitory Facility, Pantry / Kitchen Area, Kantin (Canteen) Photo, Pantry Room Photo

### Community 14 - "Recreation Facility Images"
Cohesion: 0.60
Nodes (5): Dormitory Facilities, Dormitory Music Room Facility, Musholla Facility Photo, Ruang Musik (Music Room Photo), Musholla (Prayer Room)

### Community 15 - "Dormitory Staff & Org"
Cohesion: 0.67
Nodes (4): Dormitory Staff / Organization, Staff Meeting / Presentation, Whiteboard Presentation, Organisasi - Staff Meeting Photo

### Community 17 - "Outdoor Facilities Images"
Cohesion: 0.67
Nodes (4): Dormitory Balcony/Outdoor Area, Badminton Court Photo, Balkon (Balcony) Photo, Images Directory

### Community 18 - "Node.js / Tailwind Config"
Cohesion: 0.50
Nodes (3): dependencies, tailwindcss, @tailwindcss/cli

### Community 20 - "Error UI Feedback"
Cohesion: 0.67
Nodes (3): Error State User Feedback, Humorous UI Element, Error GIF Animation

### Community 21 - "Doremi Brand Identity"
Cohesion: 1.00
Nodes (3): Doremi Brand Identity, Dormitory Building, Doremi App Logo

### Community 22 - "Market & Inventory"
Cohesion: 1.00
Nodes (3): Dormitory Market Facility, Dormitory Market/Canteen Photo, Inventory Management

### Community 84 - "dbFetchAll"
Cohesion: 0.09
Nodes (48): fetchDashboardEmergencyList(), fetchDashboardGenderStats(), fetchDashboardMaintenanceCounts(), fetchDashboardMyTasks(), fetchDashboardPenghuniIzinAktif(), fetchDashboardPenghuniMaintenanceSummary(), fetchDashboardPenghuniPaketSummary(), fetchDashboardPengurusStats() (+40 more)

### Community 85 - "Database Schema"
Cohesion: 0.07
Nodes (29): Auth Helpers, Authentication, Coding Conventions, Color Palette, CSS / Styling Rules, Current PHP Module Layout, Database Layer: Stored Procedures, UDFs & Triggers, Database Schema (+21 more)

### Community 86 - "Database Schema"
Cohesion: 0.07
Nodes (29): Auth Helpers, Authentication, Coding Conventions, Color Palette, CSS / Styling Rules, Current PHP Module Layout, Database Layer: Stored Procedures, UDFs & Triggers, Database Schema (+21 more)

### Community 87 - "Database Dump — `astrador_doremiapp`"
Cohesion: 0.17
Nodes (11): Database Dump — `astrador_doremiapp`, inoutpenghuni, inventaris, kamar, maintenance, paket, pengambilanpaket, penghuni (+3 more)

### Community 89 - "Doremi App — Agent Instructions"
Cohesion: 0.25
Nodes (7): Codebase Conventions, Community map (what lives where), Doremi App — Agent Instructions, How to use the graph, Knowledge Graph (graphify), Project Overview, Python interpreter for graphify

### Community 90 - "Graphify — Codebase Knowledge Graph"
Cohesion: 0.29
Nodes (6): Commands, Community map, Files, Graph overview, Graphify — Codebase Knowledge Graph, When to use

### Community 91 - "Karpathy behavioral guidelines"
Cohesion: 0.33
Nodes (5): 1. Think Before Coding, 2. Simplicity First, 3. Surgical Changes, 4. Goal-Driven Execution, Karpathy behavioral guidelines

### Community 92 - "Karpathy behavioral guidelines"
Cohesion: 0.33
Nodes (5): 1. Think Before Coding, 2. Simplicity First, 3. Surgical Changes, 4. Goal-Driven Execution, Karpathy behavioral guidelines

## Knowledge Gaps
- **123 isolated node(s):** `name`, `description`, `type`, `authors`, `vlucas/phpdotenv` (+118 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **46 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `dbFetchOne()` connect `In/Out Request Management` to `Resident (Penghuni) Helpers`, `Maintenance Report Validation`, `Authentication Flow`, `Maintenance Helpers`, `dbFetchAll`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Why does `dbFetchAll()` connect `dbFetchAll` to `In/Out Request Management`, `Resident (Penghuni) Helpers`, `Maintenance Report Validation`?**
  _High betweenness centrality (0.067) - this node is a cross-community bridge._
- **Are the 53 inferred relationships involving `dbFetchAll()` (e.g. with `fetchDashboardEmergencyList()` and `fetchDashboardGenderStats()`) actually correct?**
  _`dbFetchAll()` has 53 INFERRED edges - model-reasoned connections that need verification._
- **Are the 29 inferred relationships involving `dbExecute()` (e.g. with `confirmInOutEntry()` and `confirmInOutExit()`) actually correct?**
  _`dbExecute()` has 29 INFERRED edges - model-reasoned connections that need verification._
- **Are the 27 inferred relationships involving `dbFetchOne()` (e.g. with `fetchPenghuniByEmail()` and `fetchPetugasByEmail()`) actually correct?**
  _`dbFetchOne()` has 27 INFERRED edges - model-reasoned connections that need verification._
- **Are the 10 inferred relationships involving `n()` (e.g. with `A()` and `_e()`) actually correct?**
  _`n()` has 10 INFERRED edges - model-reasoned connections that need verification._
- **Are the 5 inferred relationships involving `se()` (e.g. with `c()` and `D()`) actually correct?**
  _`se()` has 5 INFERRED edges - model-reasoned connections that need verification._