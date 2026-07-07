# Doremi App — Agent Instructions

## Project Overview

Doremi is a PHP-native dormitory management web application. It handles resident management, room assignments, packages, in/out logging, maintenance reports, and inventory for a student dormitory.

Stack: PHP (no framework), MySQL, Tailwind CSS, DataTables, Laragon (local dev server).

## Knowledge Graph (graphify)

This project has a pre-built knowledge graph at `graphify-out/` covering all 102 source files (82 code, 5 docs, 15 images).

**Graph stats:** 375 nodes · 577 edges · 84 communities

**Key god nodes (most connected):**
- `dbExecute()` — all write operations route through this
- `dbFetchOne()` / `dbFetchAll()` — primary query helpers, bridge multiple feature communities
- `validatePenghuniCommonInput()` — shared resident validation across flows

### How to use the graph

Before answering codebase questions or starting a task, query the graph:

```bash
# Answer a question using graph traversal
graphify query "<question>"

# Find how two concepts relate
graphify path "<ConceptA>" "<ConceptB>"

# Get a focused explanation of one concept
graphify explain "<concept>"
```

Use `graphify-out/GRAPH_REPORT.md` for broad architecture review.

After modifying code, keep the graph current (free, no API cost):

```bash
graphify update .
```

### Community map (what lives where)

| Community | Contents |
|---|---|
| Database & Auth Helpers | `database/*.php`, `auth/helpers.php`, role definitions |
| In/Out Request Management | all `inout` CRUD + business logic |
| Maintenance Report Validation | maintenance report flow + validation |
| Package (Paket) Helpers | package tracking helpers |
| Resident (Penghuni) Helpers | resident identity/floor/NIM validation helpers |
| Room (Kamar) Helpers | room number building and parsing |
| Authentication Flow | login/email auth, role-based redirect |
| CSRF Security Layer | `csrf_field()`, `csrf_token()`, `csrf_validate()` |

## Codebase Conventions

- No framework — routing is file-based (`penghuni/create.php`, etc.)
- All DB access goes through `database/query.php` helpers (`dbExecute`, `dbFetchOne`, `dbFetchAll`)
- Domain helpers live in `helpers.php` files per feature module
- Validation lives in `validation.php` files per feature module
- CSRF protection is required on all mutating forms
- UI feedback uses `images/gif/error.gif` and `images/gif/success.gif`

## Python interpreter for graphify

The graphify tool was installed under Python 3.12. The interpreter path is saved at:

```
graphify-out/.graphify_python
```

Use it for any graphify Python calls:

```bash
$(cat graphify-out/.graphify_python) -m graphify <subcommand>
```

On Windows:
```powershell
$PYTHON = Get-Content "graphify-out\.graphify_python"
& $PYTHON -m graphify <subcommand>
```
