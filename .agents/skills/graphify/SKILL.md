---
name: graphify
description: Knowledge graph for the doremi-app codebase. 375 nodes, 577 edges, 84 communities extracted from 102 files. Use graphify query/path/explain before browsing source files.
alwaysApply: true
---

# Graphify — Codebase Knowledge Graph

This project has a pre-built knowledge graph at `graphify-out/`. Always query it before browsing raw source files.

## When to use

- Before answering any question about how code is structured, where a feature lives, or how two things relate
- Before starting any task that touches more than one file
- Instead of grepping/reading files to orient yourself — the graph already has the relationships

## Commands

```bash
# Answer a codebase question via graph traversal (BFS, broad context)
graphify query "<question>"

# Trace how two concepts connect
graphify path "<ConceptA>" "<ConceptB>"

# Focused explanation of one node
graphify explain "<concept>"

# Keep graph current after editing code (free, AST-only, no API cost)
graphify update .
```

On Windows, use the saved interpreter:
```powershell
$PYTHON = Get-Content "graphify-out\.graphify_python"
& $PYTHON -m graphify query "<question>"
```

## Graph overview

| Stat | Value |
|---|---|
| Nodes | 375 |
| Edges | 577 |
| Communities | 84 |

**God nodes (highest connectivity — touch these carefully):**
- `dbExecute()` — 20 edges, all write operations route through here
- `dbFetchOne()` — 17 edges, bridges In/Out, Maintenance, and Auth
- `dbFetchAll()` — 16 edges, primary list-fetch helper
- `validatePenghuniCommonInput()` — 11 edges, shared across resident flows

## Community map

| Community | What's inside |
|---|---|
| Database & Auth Helpers | `database/*.php`, `auth/helpers.php`, role constants |
| In/Out Request Management | all in/out CRUD + business logic (45 nodes) |
| Maintenance Report Validation | maintenance flow, validation, claim/complete lifecycle |
| Package (Paket) Helpers | package type helpers, photo handling, status logic |
| Resident (Penghuni) Helpers | NIM validation, floor/gender rules, duplicate identity detection |
| Room (Kamar) Helpers | room number building, floor/bagian parsing |
| Authentication Flow | email login, role-based redirect, session management |
| CSRF Security Layer | `csrf_field()`, `csrf_token()`, `csrf_validate()` |
| DataTables JS Library | minified vendor JS (ignore for code analysis) |

## Files

- `graphify-out/graph.json` — raw graph data
- `graphify-out/GRAPH_REPORT.md` — full audit report with god nodes, communities, surprising connections
- `graphify-out/graph.html` — interactive browser visualization
