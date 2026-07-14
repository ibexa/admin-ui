# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

`ibexa/admin-ui` is the Ibexa DXP **Back Office** bundle — the main admin UI package. It is
developed in place inside a DXP project checkout (`vendor/ibexa/admin-ui` is its own git
repo). Other `vendor/ibexa/*` packages (product-catalog, order-management, …) extend the
Back Office through this package's extension points; UI work frequently spans several of
those repos on coordinated branches (feature branches `IBX-<ticket>-<slug>`; DS integration
branch: `ds-development`). Do not commit unless explicitly asked.

## Commands

Run inside this package:

```bash
yarn test              # prettier --check + tsc --noEmit + eslint (JS/TS/SCSS static checks)
yarn fix               # auto-fix prettier/eslint
composer test          # PHPUnit: test-unit (phpunit.xml) + test-integration (phpunit.integration.xml)
composer test-unit     # single suite; single test: vendor/bin/phpunit -c phpunit.xml --filter <TestName>
composer phpstan       # phpstan.neon (+ large baseline — don't add to it)
composer check-cs      # php-cs-fixer dry-run (fix-cs to apply)
```

**Asset compilation does NOT happen here.** Webpack Encore runs from the DXP project root,
which discovers and merges every bundle's `Resources/encore/ibexa.config.js` (via
`@ibexa/frontend-config`):

```bash
cd <dxp-root>
yarn ibexa:dev         # one-shot dev build of all ibexa bundle entries
yarn ibexa:watch       # watch mode
yarn ibexa:build       # production
```

Output lands in `<dxp-root>/public/build/`; static bundle assets are published via
`assets:install`. After config/service/template-location changes: `php bin/console
cache:clear` in the DXP root.

## Layout (PSR-4)

| Namespace | Path | Role |
|---|---|---|
| `Ibexa\Contracts\AdminUi\` | `src/contracts/` | Public extension contracts (Menu, Component, Tab, UDW, Controller, …) — what other packages depend on |
| `Ibexa\AdminUi\` | `src/lib/` | Core logic: menu builders, components, tabs, UI config providers, UDW, form logic |
| `Ibexa\Bundle\AdminUi\` | `src/bundle/` | Symfony wiring: controllers, DI, routing, forms, Twig extensions + all `Resources/` (views, public assets, encore) and `ui-dev/` (React) |

Tests: `tests/bundle/`, `tests/integration/`, `tests/lib/`; Behat in `features/`.

## View layer (Twig + design engine)

- Templates live under `src/bundle/Resources/views/themes/admin/` and are referenced via
  the virtual **`@ibexadesign`** namespace (ibexa/design-engine). The `admin` design falls
  back to `standard`, so any bundle can override any admin template by shipping the same
  relative path in its own theme dir. In YAML service config the namespace is escaped as
  `@@ibexadesign`.
- `ui/` is the shell: `layout.html.twig`, `edit_base.html.twig`, menus, tabs,
  `component/` (dropdowns, tables, modals, side panels…), `form_fields.html.twig`. Feature
  areas sit alongside (`content/`, `content_type/`, `account/`, `trash/`, …).
- Content-edit form/field templates are registered in
  `src/bundle/Resources/config/admin_ui_forms.yaml` (priority-ordered); view→template
  mapping in `config/views.yaml`.
- New UI must use Ibexa Design System components (`<twig:ibexa:*>` from
  design-system-twig; React `@ids-components`) rather than re-creating legacy markup.

## Frontend

Three generations coexist:

1. **Legacy vanilla JS** — `src/bundle/Resources/public/js/scripts/` (flat `admin.*.js` +
   `core/` widgets, `fieldType/` editors auto-globbed into the build, `udw/`, `sidebar/`,
   `helpers/`).
2. **SCSS** — `src/bundle/Resources/public/scss/`; entries `ibexa.scss` (~140 partials),
   `ibexa-bootstrap.scss`, `ibexa-ids-assets.scss` (bridge to the design-system `ids-assets`
   SCSS, vendored/symlinked via admin-ui-assets), `ui/ibexa-modules.scss` (React module styles).
3. **React modules** — `src/bundle/ui-dev/src/modules/`: `universal-discovery` (UDW),
   `content-tree`, `sub-items`, `multi-file-upload`, plus `common/` (shared components and
   `services/` for REST calls, notifications).

Encore wiring in `src/bundle/Resources/encore/`: `ibexa.js.config.js` defines per-page
entries (`ibexa-admin-ui-layout-js`, `-location-view-js`, `-udw-js`, …),
`ibexa.css.config.js` the CSS entries, `ibexa.config.setup.js` the aliases
(`@ibexa-admin-ui`, `@ibexa-admin-ui-modules`, …). Cross-bundle references use literal
`./vendor/ibexa/<pkg>/…` paths. Page templates load entries with
`{{ encore_entry_*_tags() }}`.

## Back-office extension points (how packages add UI)

1. **Menus** — KnpMenu builders in `src/lib/Menu/` dispatch `ConfigureMenuEvent`
   (constants: `MAIN_MENU`, `USER_MENU`, `CONTENT_SIDEBAR_RIGHT`, many `*_SIDEBAR_RIGHT` /
   anchor menus); packages subscribe to add items.
2. **Component zones** — named page zones (`dashboard-blocks`, `layout-content-after`,
   `content-edit-form-before/after`, `location-view-tabs-after`, …) rendered via the
   component registry. The in-package `Ibexa\AdminUi\Component\Registry` is deprecated in
   favor of `Ibexa\TwigComponents\Component\Registry` (ibexa/twig-components) — use the
   latter for new work.
3. **Tabs** — `TabRegistry` + tab groups (`src/lib/Tab/`, contracts in
   `src/contracts/Tab/`), configured in `services/tabs*.yaml`.
4. **UDW** — config presets in `config/universal_discovery_widget.yaml`; tabs/providers in
   `src/lib/UniversalDiscovery/`; React side in `ui-dev/src/modules/universal-discovery`.
5. **UI Config** — tagged providers in `src/lib/UI/Config/Provider/` feed the global JS
   config object available to all frontend code.
6. **Encore** — each bundle ships `Resources/encore/ibexa.config.js`; the DXP-root build
   merges them (that is how a package's JS/CSS gets on admin pages).
