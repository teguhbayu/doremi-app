# Graph Report - doremi-app  (2026-07-09)

## Corpus Check
- 101 files · ~1,213,686 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 561 nodes · 825 edges · 133 communities (87 shown, 46 thin omitted)
- Extraction: 80% EXTRACTED · 20% INFERRED · 0% AMBIGUOUS · INFERRED: 163 edges (avg confidence: 0.74)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `1d107195`
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
- [[_COMMUNITY_Room Facility Images|Room Facility Images]]
- [[_COMMUNITY_Dining Facility Images|Dining Facility Images]]
- [[_COMMUNITY_Recreation Facility Images|Recreation Facility Images]]
- [[_COMMUNITY_Dormitory Staff & Org|Dormitory Staff & Org]]
- [[_COMMUNITY_CSRF Security Layer|CSRF Security Layer]]
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
- [[_COMMUNITY_penghuni.php|penghuni.php]]
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
2. `dbExecute()` - 32 edges
3. `dbFetchOne()` - 30 edges
4. `n()` - 19 edges
5. `se()` - 14 edges
6. `he()` - 13 edges
7. `x()` - 12 edges
8. `validatePenghuniCommonInput()` - 11 edges
9. `Database Dump — `astrador_doremiapp`` - 11 edges
10. `ne()` - 10 edges

## Surprising Connections (you probably didn't know these)
- `penghuni_find_identity_matches()` --calls--> `fetchPenghuniIdentityRows()`  [INFERRED]
  dashboard/penghuni/helpers.php → database/penghuni.php
- `penghuni_validate_room_assignment()` --calls--> `fetchKamarForPenghuniAssignment()`  [INFERRED]
  dashboard/penghuni/helpers.php → database/penghuni.php
- `penghuni_validate_room_assignment()` --calls--> `fetchPenghuniRoomOccupantSummary()`  [INFERRED]
  dashboard/penghuni/helpers.php → database/penghuni.php
- `authAttemptPasswordLogin()` --calls--> `findAuthUserByEmail()`  [INFERRED]
  auth/helpers.php → database/auth.php
- `authAttemptEmailLogin()` --calls--> `findAuthUserByEmail()`  [INFERRED]
  auth/helpers.php → database/auth.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Database Layer: Stored Procedures + UDFs + Triggers** — _agents_skills_doremi_app_context_skill_stored_procedures, _agents_skills_doremi_app_context_skill_udfs, _agents_skills_doremi_app_context_skill_triggers [EXTRACTED 1.00]
- **Domain DB Helper Files (auth, paket, inout, maintenance, penghuni, photos)** — _agents_skills_doremi_app_context_skill_database_auth_php, _agents_skills_doremi_app_context_skill_database_paket_php, _agents_skills_doremi_app_context_skill_database_inout_php, _agents_skills_doremi_app_context_skill_database_maintenance_php, _agents_skills_doremi_app_context_skill_database_penghuni_php, _agents_skills_doremi_app_context_skill_database_photos_php [EXTRACTED 1.00]
- **Karpathy Coding Guideline Principles** — _agents_skills_coding_guideline_skill_think_before_coding, _agents_skills_coding_guideline_skill_simplicity_first, _agents_skills_coding_guideline_skill_surgical_changes, _agents_skills_coding_guideline_skill_goal_driven_execution [EXTRACTED 1.00]

## Communities (133 total, 46 thin omitted)

### Community 0 - "DataTables JS Library"
Cohesion: 0.08
Nodes (60): A(), ae(), at(), B(), be(), c(), ce(), ct() (+52 more)

### Community 1 - "In/Out Request Management"
Cohesion: 0.07
Nodes (53): fetchDashboardPenghuniIzinAktif(), checkKamarActive(), checkRuanganActive(), createInventaris(), deleteInventaris(), fetchActiveKamarOptions(), fetchActiveRuanganOptions(), fetchAllInventarisWithLokasi() (+45 more)

### Community 3 - "Resident (Penghuni) Helpers"
Cohesion: 0.18
Nodes (20): penghuni_allowed_floors(), penghuni_duplicate_identity_message(), penghuni_find_identity_matches(), penghuni_gender_label(), penghuni_is_valid_nim(), penghuni_is_valid_phone(), penghuni_nim_max_length(), penghuni_nim_min_length() (+12 more)

### Community 4 - "Maintenance Report Validation"
Cohesion: 0.16
Nodes (14): mysqli, validateMaintenanceReportInput(), checkMaintenanceTargetExists(), checkMaintenanceTechnicianOwnership(), claimMaintenanceReport(), completeMaintenanceReport(), createMaintenanceReport(), deleteMaintenanceReport() (+6 more)

### Community 5 - "Package (Paket) Helpers"
Cohesion: 0.11
Nodes (12): paket_allowed_types(), paket_cleanup_legacy_photo(), paket_is_valid_length(), paket_normalize_datetime(), paket_normalize_type(), paket_store_photo(), paket_type_badge_class(), paket_type_label() (+4 more)

### Community 6 - "Authentication Flow"
Cohesion: 0.26
Nodes (7): authAttemptEmailLogin(), authAttemptPasswordLogin(), mysqli, fetchPenghuniByEmail(), fetchPetugasByEmail(), findAuthUserByEmail(), mysqli

### Community 7 - "In/Out Input & Validation"
Cohesion: 0.22
Nodes (4): validateInOutRequestInput(), formatDateTime(), normalizeDateTimeInputValue(), textLength()

### Community 8 - "Composer Project Config"
Cohesion: 0.22
Nodes (8): authors, description, name, require, google/apiclient, respect/validation, vlucas/phpdotenv, type

### Community 10 - "Room (Kamar) Helpers"
Cohesion: 0.60
Nodes (4): kamar_build_nomor(), kamar_extract_bagian(), kamar_has_lantai_prefix(), kamar_normalize_segment()

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

### Community 16 - "CSRF Security Layer"
Cohesion: 0.83
Nodes (3): csrf_field(), csrf_token(), csrf_validate()

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
Cohesion: 0.08
Nodes (55): fetchDashboardEmergencyList(), fetchDashboardGenderStats(), fetchDashboardMaintenanceCounts(), fetchDashboardMyTasks(), fetchDashboardPenghuniMaintenanceSummary(), fetchDashboardPenghuniPaketSummary(), fetchDashboardPengurusStats(), fetchMaintenanceStatusPie() (+47 more)

### Community 85 - "Database Schema"
Cohesion: 0.07
Nodes (29): Auth Helpers, Authentication, Coding Conventions, Color Palette, CSS / Styling Rules, Current PHP Module Layout, Database Layer: Stored Procedures, UDFs & Triggers, Database Schema (+21 more)

### Community 86 - "Database Schema"
Cohesion: 0.07
Nodes (29): Auth Helpers, Authentication, Coding Conventions, Color Palette, CSS / Styling Rules, Current PHP Module Layout, Database Layer: Stored Procedures, UDFs & Triggers, Database Schema (+21 more)

### Community 87 - "Database Dump — `astrador_doremiapp`"
Cohesion: 0.17
Nodes (11): Database Dump — `astrador_doremiapp`, inoutpenghuni, inventaris, kamar, maintenance, paket, pengambilanpaket, penghuni (+3 more)

### Community 88 - "penghuni.php"
Cohesion: 0.33
Nodes (10): createPenghuni(), fetchActiveKamarWithOccupancy(), fetchKamarForPenghuniAssignment(), fetchPenghuniById(), fetchPenghuniIdentityRows(), fetchPenghuniList(), fetchPenghuniRoomOccupantSummary(), mysqli (+2 more)

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
- **122 isolated node(s):** `name`, `description`, `type`, `authors`, `vlucas/phpdotenv` (+117 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **46 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `dbFetchOne()` connect `In/Out Request Management` to `Maintenance Report Validation`, `Package (Paket) Helpers`, `Authentication Flow`, `dbFetchAll`, `penghuni.php`?**
  _High betweenness centrality (0.068) - this node is a cross-community bridge._
- **Why does `dbFetchAll()` connect `dbFetchAll` to `penghuni.php`, `In/Out Request Management`, `Maintenance Report Validation`?**
  _High betweenness centrality (0.065) - this node is a cross-community bridge._
- **Why does `checkPenghuniExists()` connect `Package (Paket) Helpers` to `In/Out Request Management`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 53 inferred relationships involving `dbFetchAll()` (e.g. with `fetchDashboardEmergencyList()` and `fetchDashboardGenderStats()`) actually correct?**
  _`dbFetchAll()` has 53 INFERRED edges - model-reasoned connections that need verification._
- **Are the 28 inferred relationships involving `dbExecute()` (e.g. with `confirmInOutEntry()` and `confirmInOutExit()`) actually correct?**
  _`dbExecute()` has 28 INFERRED edges - model-reasoned connections that need verification._
- **Are the 26 inferred relationships involving `dbFetchOne()` (e.g. with `fetchPenghuniByEmail()` and `fetchPetugasByEmail()`) actually correct?**
  _`dbFetchOne()` has 26 INFERRED edges - model-reasoned connections that need verification._
- **Are the 10 inferred relationships involving `n()` (e.g. with `A()` and `_e()`) actually correct?**
  _`n()` has 10 INFERRED edges - model-reasoned connections that need verification._