---
name: bo-locate
description: Map a Back Office (admin) page, view, or UI fragment to the code that renders it — owning vendor/ibexa package, Twig templates, controllers/routes, JS/React modules, SCSS. Use when you need to find where admin UI lives before changing it, or as phase 1 of bo-new-ui.
---

# Locating Back Office UI

Goal: from a URL or a screenshot fragment, produce: owning package(s) → template file(s) →
controller/route → JS/React entry → SCSS partial(s). Work from the DXP root
(`IDS_DXP_ROOT` = `../../..` from this repo); search across `vendor/ibexa/*/src`.

## Techniques (fastest first)

1. **Route → controller → template** (when you have the URL):
   ```bash
   php bin/console debug:router --show-controllers | grep '<path-fragment>'
   ```
   then open the controller — the `render(...)`/view configuration names the template.
   Content pages use `config/views.yaml`-style view rules instead of direct renders.

2. **Grep the visible string**: exact UI text is usually a `|trans|desc('Text')` default —
   ```bash
   grep -rn "desc('Exact text" vendor/ibexa/*/src --include="*.twig"
   ```
   (also try the translation files under `Resources/translations/` to get the key, then
   grep the key).

3. **Grep the DOM class**: inspect the page (browser devtools or `bo-verify-live`
   snapshot), take a distinctive `ibexa-*` class, grep `vendor/ibexa/*/src` for it —
   lands in the template (markup) and SCSS partial at once.

4. **Symfony profiler** (dev env): open the page, then the profiler's Twig panel lists
   every rendered template in order — definitive for pages assembled from many partials.
   (`https://<host>/_profiler/latest?panel=twig`)

5. **Encore entry → package**: page source `<script src="/build/ibexa-<entry>-js…">` →
   grep the entry name across `vendor/ibexa/*/src/bundle/Resources/encore/` to find the
   owning package and its JS file list.

6. **Injected UI** (blocks that appear "inside" another package's page): grep the zone
   name (`dashboard-blocks`, `location-view-tabs-after`, …) or the tab identifier across
   `vendor/ibexa/*/src` — menu items via `ConfigureMenuEvent` subscribers, zone components
   via the twig-components Registry, tabs via `TabRegistry`.

## Output format

Report: owning package + branch, then per concern: template path(s), controller::action +
route name, JS/React location + Encore entry, SCSS partial(s). Note anything rendered by a
DS component (don't relocate those — they belong to design-system-twig). If the fragment
appears on several pages, list them — the spec must decide which are in scope.
