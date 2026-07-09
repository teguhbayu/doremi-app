# Graph Report - D:/utils/laragon/www/doremi-app  (2026-07-07)

## Corpus Check
- Large corpus: 102 files · ~1,208,866 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 375 nodes · 577 edges · 84 communities (79 shown, 5 thin omitted)
- Extraction: 82% EXTRACTED · 18% INFERRED · 0% AMBIGUOUS · INFERRED: 101 edges (avg confidence: 0.72)
- Token cost: 5,012 input · 1,490 output

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

## God Nodes (most connected - your core abstractions)
1. `Doremi App Context Skill (agents)` - 23 edges
2. `dbExecute()` - 20 edges
3. `n()` - 19 edges
4. `dbFetchOne()` - 17 edges
5. `dbFetchAll()` - 16 edges
6. `se()` - 14 edges
7. `he()` - 13 edges
8. `x()` - 12 edges
9. `validatePenghuniCommonInput()` - 11 edges
10. `ne()` - 10 edges

## Surprising Connections (you probably didn't know these)
- `DB Data: petugas rows` --semantically_similar_to--> `DB Table: petugas`  [INFERRED] [semantically similar]
  database/database_dump.md → .agents/skills/doremi-app-context/SKILL.md
- `DB Data: penghuni rows` --semantically_similar_to--> `DB Table: penghuni`  [INFERRED] [semantically similar]
  database/database_dump.md → .agents/skills/doremi-app-context/SKILL.md
- `DB Data: kamar rows` --semantically_similar_to--> `DB Table: kamar`  [INFERRED] [semantically similar]
  database/database_dump.md → .agents/skills/doremi-app-context/SKILL.md
- `DB Data: paket rows` --semantically_similar_to--> `DB Table: paket`  [INFERRED] [semantically similar]
  database/database_dump.md → .agents/skills/doremi-app-context/SKILL.md
- `DB Data: maintenance rows` --semantically_similar_to--> `DB Table: maintenance`  [INFERRED] [semantically similar]
  database/database_dump.md → .agents/skills/doremi-app-context/SKILL.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Database Layer: Stored Procedures + UDFs + Triggers** — _agents_skills_doremi_app_context_skill_stored_procedures, _agents_skills_doremi_app_context_skill_udfs, _agents_skills_doremi_app_context_skill_triggers [EXTRACTED 1.00]
- **Domain DB Helper Files (auth, paket, inout, maintenance, penghuni, photos)** — _agents_skills_doremi_app_context_skill_database_auth_php, _agents_skills_doremi_app_context_skill_database_paket_php, _agents_skills_doremi_app_context_skill_database_inout_php, _agents_skills_doremi_app_context_skill_database_maintenance_php, _agents_skills_doremi_app_context_skill_database_penghuni_php, _agents_skills_doremi_app_context_skill_database_photos_php [EXTRACTED 1.00]
- **Karpathy Coding Guideline Principles** — _agents_skills_coding_guideline_skill_think_before_coding, _agents_skills_coding_guideline_skill_simplicity_first, _agents_skills_coding_guideline_skill_surgical_changes, _agents_skills_coding_guideline_skill_goal_driven_execution [EXTRACTED 1.00]

## Communities (84 total, 5 thin omitted)

### Community 0 - "DataTables JS Library"
Cohesion: 0.08
Nodes (60): A(), ae(), at(), B(), be(), c(), ce(), ct() (+52 more)

### Community 1 - "In/Out Request Management"
Cohesion: 0.11
Nodes (40): confirmInOutEntry(), confirmInOutExit(), countActiveInOutRequests(), createInOutRequest(), fetchAllInOutLogs(), fetchInOutHistoryForPenghuni(), fetchOutsideInOutRequests(), fetchPendingInOutRequests() (+32 more)

### Community 2 - "Database & Auth Helpers"
Cohesion: 0.08
Nodes (34): auth/helpers.php, User Roles (PENGURUS/SIGAP/VIRTUS/MAINTENANCE/PENGHUNI), database/auth.php, database/inout.php, database/maintenance.php, database/paket.php, database/penghuni.php, database/photos.php (+26 more)

### Community 3 - "Resident (Penghuni) Helpers"
Cohesion: 0.18
Nodes (20): penghuni_allowed_floors(), penghuni_duplicate_identity_message(), penghuni_find_identity_matches(), penghuni_gender_label(), penghuni_is_valid_nim(), penghuni_is_valid_phone(), penghuni_nim_max_length(), penghuni_nim_min_length() (+12 more)

### Community 4 - "Maintenance Report Validation"
Cohesion: 0.13
Nodes (17): mysqli, validateMaintenanceReportInput(), checkMaintenanceTargetExists(), checkMaintenanceTechnicianOwnership(), claimMaintenanceReport(), completeMaintenanceReport(), createMaintenanceReport(), deleteMaintenanceReport() (+9 more)

### Community 5 - "Package (Paket) Helpers"
Cohesion: 0.12
Nodes (11): paket_allowed_types(), paket_cleanup_legacy_photo(), paket_is_valid_length(), paket_normalize_datetime(), paket_normalize_type(), paket_store_photo(), paket_type_badge_class(), paket_type_label() (+3 more)

### Community 6 - "Authentication Flow"
Cohesion: 0.26
Nodes (7): authAttemptEmailLogin(), authAttemptPasswordLogin(), mysqli, fetchPenghuniByEmail(), fetchPetugasByEmail(), findAuthUserByEmail(), mysqli

### Community 7 - "In/Out Input & Validation"
Cohesion: 0.22
Nodes (4): validateInOutRequestInput(), formatDateTime(), normalizeDateTimeInputValue(), textLength()

### Community 8 - "Composer Project Config"
Cohesion: 0.22
Nodes (8): authors, description, name, require, google/apiclient, respect/validation, vlucas/phpdotenv, type

### Community 9 - "Coding Guidelines"
Cohesion: 0.40
Nodes (6): Coding Guideline Skill (agents), Goal-Driven Execution Principle, Simplicity First Principle, Surgical Changes Principle, Think Before Coding Principle, Coding Guideline Skill (claude)

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

## Knowledge Gaps
- **29 isolated node(s):** `name`, `description`, `type`, `authors`, `vlucas/phpdotenv` (+24 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **5 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `dbFetchOne()` connect `In/Out Request Management` to `Maintenance Report Validation`, `Authentication Flow`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Why does `checkPenghuniExists()` connect `In/Out Request Management` to `Package (Paket) Helpers`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `validatePaketInput()` connect `Package (Paket) Helpers` to `In/Out Request Management`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Are the 16 inferred relationships involving `dbExecute()` (e.g. with `confirmInOutEntry()` and `confirmInOutExit()`) actually correct?**
  _`dbExecute()` has 16 INFERRED edges - model-reasoned connections that need verification._
- **Are the 10 inferred relationships involving `n()` (e.g. with `A()` and `_e()`) actually correct?**
  _`n()` has 10 INFERRED edges - model-reasoned connections that need verification._
- **Are the 13 inferred relationships involving `dbFetchOne()` (e.g. with `fetchPenghuniByEmail()` and `fetchPetugasByEmail()`) actually correct?**
  _`dbFetchOne()` has 13 INFERRED edges - model-reasoned connections that need verification._
- **Are the 11 inferred relationships involving `dbFetchAll()` (e.g. with `fetchAllInOutLogs()` and `fetchInOutHistoryForPenghuni()`) actually correct?**
  _`dbFetchAll()` has 11 INFERRED edges - model-reasoned connections that need verification._