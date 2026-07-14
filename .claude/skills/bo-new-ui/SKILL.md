---
name: bo-new-ui
description: End-to-end pipeline for implementing a Back Office UI design — Figma frame or screenshot in, working admin UI out (Twig/SCSS/JS in admin-ui or another vendor/ibexa package), verified live in the running DXP via Playwright. Use whenever asked to implement/build/change Back Office (admin) UI from a design, mockup, screenshot, or Figma link.
---

# Back Office UI from a design

You are implementing a design in the running admin application — not building a design-system
component (for a NEW reusable DS component, use the `ids-new-component` pipeline in the React
DS repo instead; this pipeline *consumes* DS components).

## Before anything

1. Roots: this package sits inside the DXP checkout, so `IDS_DXP_ROOT` = `../../..` from
   this repo (verify: it has `composer.json` + `vendor/ibexa/`). Sibling packages:
   `<IDS_DXP_ROOT>/vendor/ibexa/*` — each its own git repo; run `git branch --show-current`
   in every repo you touch and report pre-existing dirty state.
2. Confirm with the user: ticket (`IBX-…`), target branch plan, and the page/context the
   design belongs to (URL if they have one).

## Phases and gates

| # | Phase | How | Gate |
|---|---|---|---|
| 1 | Locate | `bo-locate` skill — map the design to owning package, templates, modules, routes | — |
| 2 | Spec | write `docs/ui-specs/<slug>.spec.md` in the owning package, per [references/spec-template.md](references/spec-template.md) | **Gate 1 (mandatory):** user approves spec + answers its open questions |
| 3 | Implement | [references/implementation-notes.md](references/implementation-notes.md) — DS components first, follow neighbors | — |
| 4 | Build + live verify | `bo-verify-live` skill — rebuild assets, drive the real admin with Playwright, compare against the design | **Gate 2:** side-by-side screenshots; user signs off |
| 5 | Static checks | this package: `yarn test`, `composer test`, `composer phpstan`, `composer check-cs`; same set in any other touched package | — |

## Hard rules

- Spec drift reopens Gate 1: contract changes (markup plan, affected files, behavior) go
  back into the spec and to the user before continuing.
- Never commit or push. End every phase with per-repo changed-file lists and verbatim check
  results.
- New UI uses Ibexa Design System components (`<twig:ibexa:*>`) — do not hand-roll markup
  that a DS component provides, and never hand-write `ids-*` classes (they belong to DS
  components only; app-level classes are `ibexa-*` BEM).
- Screenshots/temp files go to the session scratchpad, never into any repo.
