# Graph Report - doremi-app  (2026-07-15)

## Corpus Check
- 112 files · ~1,254,566 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 585 nodes · 857 edges · 134 communities (88 shown, 46 thin omitted)
- Extraction: 80% EXTRACTED · 20% INFERRED · 0% AMBIGUOUS · INFERRED: 174 edges (avg confidence: 0.75)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0d99017c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- DataTables JS Library
- In/Out Request Management
- Database & Auth Helpers
- Resident (Penghuni) Helpers
- Maintenance Report Validation
- Package (Paket) Helpers
- Authentication Flow
- In/Out Input & Validation
- Composer Project Config
- Coding Guidelines
- Room (Kamar) Helpers
- Maintenance Helpers
- Room Facility Images
- Dining Facility Images
- Recreation Facility Images
- Dormitory Staff & Org
- Outdoor Facilities Images
- Node.js / Tailwind Config
- Error UI Feedback
- Doremi Brand Identity
- Market & Inventory
- Bathroom Facility Images
- Dev Start Scripts
- Auditorium Event Photo
- Basketball Court Photo
- Success UI Feedback
- dbFetchAll
- Database Schema
- Database Schema
- Database Dump — `astrador_doremiapp`
- Doremi App — Agent Instructions
- Graphify — Codebase Knowledge Graph
- Karpathy behavioral guidelines
- Karpathy behavioral guidelines
- CLAUDE.md
- Goal-Driven Execution Principle
- Simplicity First Principle
- Surgical Changes Principle
- Think Before Coding Principle
- User Roles (PENGURUS/SIGAP/VIRTUS/MAINTENANCE/PENGHUNI)
- database/auth.php
- database/inout.php
- database/maintenance.php
- database/paket.php
- database/penghuni.php
- database/photos.php
- database/query.php Shared Helpers
- Doremi App Context Skill (agents)
- Stored Procedures Layer
- DB Table: inoutpenghuni
- DB Table: inventaris
- DB Table: kamar
- DB Table: maintenance
- DB Table: paket
- DB Table: pengambilanpaket
- DB Table: penghuni
- DB Table: petugas
- DB Table: ruangan
- Doremi Tech Stack
- Database Triggers
- User-Defined Functions (UDFs)
- Coding Guideline Skill (claude)
- Doremi App Context Skill (claude)
- Doremi App Database Dump
- DB Data: inoutpenghuni rows
- DB Data: inventaris rows
- DB Data: kamar rows
- DB Data: maintenance rows
- DB Data: paket rows
- DB Data: pengambilanpaket rows
- DB Data: penghuni rows
- DB Data: petugas rows
- DB Data: ruangan rows

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
- `validatePaketInput()` --calls--> `checkPenghuniExists()`  [INFERRED]
  dashboard/paket/validation.php → database/paket.php
- `validatePenghuniInputSchema()` --calls--> `firstFieldError()`  [INFERRED]
  dashboard/penghuni/validation.php → utils/validation_helpers.php
- `authAttemptPasswordLogin()` --calls--> `findAuthUserByEmail()`  [INFERRED]
  auth/helpers.php → database/auth.php
- `authAttemptEmailLogin()` --calls--> `findAuthUserByEmail()`  [INFERRED]
  auth/helpers.php → database/auth.php
- `validateInOutRequestInput()` --calls--> `textLength()`  [INFERRED]
  dashboard/inout/validation.php → utils/format.php

## Import Cycles
- None detected.

## Communities (134 total, 46 thin omitted)

### Community 0 - "DataTables JS Library"
Cohesion: 0.08
Nodes (59): A(), ae(), at(), B(), be(), c(), ce(), ct() (+51 more)

### Community 1 - "In/Out Request Management"
Cohesion: 0.08
Nodes (48): confirmInOutEntry(), confirmInOutExit(), countActiveInOutRequests(), createInOutRequest(), fetchAllInOutLogs(), fetchInOutHistoryForPenghuni(), fetchOutsideInOutRequests(), fetchPendingInOutRequests() (+40 more)

### Community 3 - "Resident (Penghuni) Helpers"
Cohesion: 0.12
Nodes (30): penghuni_allowed_floors(), penghuni_duplicate_identity_message(), penghuni_find_identity_matches(), penghuni_gender_label(), penghuni_is_valid_nim(), penghuni_is_valid_phone(), penghuni_nim_max_length(), penghuni_nim_min_length() (+22 more)

### Community 4 - "Maintenance Report Validation"
Cohesion: 0.11
Nodes (19): mysqli, validateMaintenanceReportInput(), checkMaintenanceTargetExists(), checkMaintenanceTechnicianOwnership(), claimMaintenanceReport(), completeMaintenanceReport(), createMaintenanceReport(), deleteMaintenanceReport() (+11 more)

### Community 5 - "Package (Paket) Helpers"
Cohesion: 0.13
Nodes (11): paket_allowed_types(), paket_cleanup_legacy_photo(), paket_is_valid_length(), paket_normalize_datetime(), paket_normalize_type(), paket_store_photo(), paket_type_badge_class(), paket_type_label() (+3 more)

### Community 6 - "Authentication Flow"
Cohesion: 0.09
Nodes (18): authAttemptEmailLogin(), authAttemptPasswordLogin(), authRedirectToDashboard(), authRedirectToLoginError(), mysqli, csrf_field(), csrf_token(), csrf_validate() (+10 more)

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
Cohesion: 0.30
Nodes (11): createPetugas(), deletePetugas(), fetchAllPetugas(), fetchPetugasById(), findPetugasDuplicateActive(), findPetugasDuplicateDeleted(), findPetugasDuplicateExcluding(), mysqli (+3 more)

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
Cohesion: 0.33
Nodes (5): dependencies, tailwindcss, @tailwindcss/cli, tailwindcss, @tailwindcss/cli

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
- **Why does `dbFetchAll()` connect `dbFetchAll` to `Maintenance Helpers`, `In/Out Request Management`, `Resident (Penghuni) Helpers`, `Maintenance Report Validation`?**
  _High betweenness centrality (0.067) - this node is a cross-community bridge._
- **Why does `checkPenghuniExists()` connect `In/Out Request Management` to `Package (Paket) Helpers`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Are the 53 inferred relationships involving `dbFetchAll()` (e.g. with `fetchDashboardEmergencyList()` and `fetchDashboardGenderStats()`) actually correct?**
  _`dbFetchAll()` has 53 INFERRED edges - model-reasoned connections that need verification._
- **Are the 29 inferred relationships involving `dbExecute()` (e.g. with `confirmInOutEntry()` and `confirmInOutExit()`) actually correct?**
  _`dbExecute()` has 29 INFERRED edges - model-reasoned connections that need verification._
- **Are the 27 inferred relationships involving `dbFetchOne()` (e.g. with `fetchPenghuniByEmail()` and `fetchPetugasByEmail()`) actually correct?**
  _`dbFetchOne()` has 27 INFERRED edges - model-reasoned connections that need verification._
- **Are the 10 inferred relationships involving `n()` (e.g. with `A()` and `_e()`) actually correct?**
  _`n()` has 10 INFERRED edges - model-reasoned connections that need verification._