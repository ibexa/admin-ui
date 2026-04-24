# DROPDOWN MIGRATION PLAN: Legacy ibexa-dropdown / c-simple-dropdown -> design-system Dropdowns

**Package**: `ibexa/admin-ui`
**Branch**: `IBX-11413-use-DS-dropdown`
**Date Created**: March 17, 2026
**Status**: Planning

---

## CRITICAL DIFFERENCES - READ FIRST

### Difference 1: This is not a class swap

Legacy Twig dropdowns, legacy vanilla JS, React dropdowns, SCSS overrides, and Behat locators are all tightly coupled.

| Legacy | Design System |
|---|---|
| `ibexa-dropdown*` | `ids-dropdown*` |
| `c-simple-dropdown*` | no direct 1:1 replacement confirmed |
| `ibexaInstance` API | `idsInstance` API |
| Bootstrap Popover positioning | Popper-based DS positioning |

**Impact:** a template-only migration will break JS behavior, styling, and tests.

---

### Difference 2: Twig and React are separate implementations

There are two independent dropdown families in `admin-ui`:

- **Twig + vanilla JS** based on `@ibexadesign/ui/component/dropdown/dropdown.html.twig`
- **React dropdowns** based on:
  - `src/bundle/ui-dev/src/modules/common/dropdown/dropdown.js`
  - `src/bundle/ui-dev/src/modules/common/simple-dropdown/simple.dropdown.js`

They must be planned separately, even if they converge on DS markup.

---

### Difference 3: Legacy Twig dropdown has more features than DS dropdown today

Legacy Twig supports patterns that are not directly covered by the current DS Twig dropdowns:

- grouped items (`choice.choices`)
- preferred choices + separator
- select-all / clear toggler
- custom selected-item and list-item templates
- item icons and selected-item icons
- mutation-driven option rebuilds
- no-results empty state with image/message
- multi-select selected-item overflow counter

**Impact:** some legacy usages can migrate directly; others need a compatibility layer, redesign, or deferral.

---

### Difference 4: Form theme migration is high blast radius

`src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig` uses `dropdown_widget.html.twig` for collapsed Symfony choice widgets.

That means migrating:

- `src/bundle/Resources/views/themes/admin/ui/form_fields/dropdown_widget.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

affects many admin forms at once.

**Rule:** do not start with form theme migration. Start with isolated direct include cases first.

---

### Difference 5: JS compatibility must be decided before broad Twig rollout

Current admin JS depends on the legacy dropdown instance and methods such as:

- `selectOption()`
- `selectFirstOption()`
- `clearCurrentSelection()`
- `getSelectedItems()`
- `canSelectOnlyOne`

**Recommended approach:** add an admin-ui compatibility bridge for DS dropdowns before migrating form-theme usage.

---

## Table of Contents

1. [Goals](#goals)
2. [Source Inventory](#source-inventory)
3. [Design System Reference](#design-system-reference)
4. [Migration Strategy](#migration-strategy)
5. [Twig Migration Phases](#twig-migration-phases)
6. [React Migration Phases](#react-migration-phases)
7. [JavaScript Migration Checklist](#javascript-migration-checklist)
8. [SCSS Migration Checklist](#scss-migration-checklist)
9. [Behat / Test Impact](#behat--test-impact)
10. [Known Gaps / Blockers](#known-gaps--blockers)
11. [Validation Commands](#validation-commands)
12. [Testing Strategy](#testing-strategy)

---

## Goals

1. Replace legacy dropdowns with DS dropdowns where feasible
2. Keep functional parity for Twig-rendered forms and React UIs
3. Update related JavaScript in the same phase as markup changes
4. Minimize regressions by starting from isolated usages
5. Keep all changes local on branch `IBX-11413-use-DS-dropdown`

---

## Source Inventory

### Legacy Twig dropdown implementation

- `src/bundle/Resources/views/themes/admin/ui/component/dropdown/dropdown.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/component/dropdown/dropdown_item.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/component/dropdown/dropdown_selected_item.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/component/dropdown/dropdown_selected_item_icon.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/form_fields/dropdown_widget.html.twig`

### Direct Twig include sites

- `src/bundle/Resources/views/themes/admin/content/tab/content.html.twig`
- `src/bundle/Resources/views/themes/admin/content_type/tab/view.html.twig`

### Form theme entry point

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

### Legacy vanilla JS

- `src/bundle/Resources/public/js/scripts/core/dropdown.js`
- `src/bundle/Resources/public/js/scripts/admin.dropdown.js`
- `src/bundle/Resources/public/js/scripts/admin.contenttype.edit.js`

### JS files coupled to dropdown selectors / instance behavior

- `src/bundle/Resources/public/js/scripts/filters.action.btns.js`
- `src/bundle/Resources/public/js/scripts/admin.notifications.filters.js`
- `src/bundle/Resources/public/js/scripts/sidebar/extra.actions.js`
- `src/bundle/Resources/public/js/scripts/fieldType/ibexa_selection.js`
- `src/bundle/Resources/public/js/scripts/fieldType/ibexa_country.js`
- `src/bundle/Resources/public/js/scripts/admin.location.add.custom_url.js`

### React dropdown components

- `src/bundle/ui-dev/src/modules/common/dropdown/dropdown.js`
- `src/bundle/ui-dev/src/modules/common/simple-dropdown/simple.dropdown.js`

### React consumers

**Full dropdown**
- `src/bundle/ui-dev/src/modules/universal-discovery/components/filters/filters.js`
- `src/bundle/ui-dev/src/modules/universal-discovery/components/content-create-widget/content.create.widget.js`

**Simple dropdown**
- `src/bundle/ui-dev/src/modules/universal-discovery/components/view-switcher/view.switcher.js`
- `src/bundle/ui-dev/src/modules/universal-discovery/components/sort-switcher/sort.switcher.js`
- `src/bundle/ui-dev/src/modules/sub-items/components/view-switcher/view.switcher.component.js`

### SCSS

**Primary definitions**
- `src/bundle/Resources/public/scss/_dropdown.scss`
- `src/bundle/Resources/public/scss/_dropdown-popover.scss`
- `src/bundle/Resources/public/scss/ui/modules/common/_simple-dropdown.scss`

**Context overrides**
- `src/bundle/Resources/public/scss/ui/modules/universal-discovery/_dropdown.scss`
- `src/bundle/Resources/public/scss/ui/modules/universal-discovery/_view-switcher.scss`
- `src/bundle/Resources/public/scss/ui/modules/sub-items-list/_main.scss`
- plus context references in filters, search, field-group, preview, details, and related SCSS files

### Behat selectors with direct legacy coupling

Examples:
- `src/lib/Behat/Component/IbexaDropdown.php`
- `src/lib/Behat/Page/RoleUpdatePage.php`
- `src/lib/Behat/Component/CreateNewPopup.php`
- `src/lib/Behat/Page/ContentViewPage.php`
- `src/lib/Behat/Component/Fields/Selection.php`
- `src/lib/Behat/Component/Fields/Country.php`

---

## Design System Reference

### DS Twig dropdowns

- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/dropdown_single/input.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/dropdown_multi/input.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/partials/base_dropdown.html.twig`

### DS TypeScript behavior

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/partials/base_dropdown/base_dropdown.ts`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/components/dropdown/dropdown_single_input.ts`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/components/dropdown/dropdown_multi_input.ts`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

### Important DS behavior notes

- DS uses `.ids-dropdown--single` / `.ids-dropdown--multi`
- DS uses `data-ids-custom-init` instead of legacy custom-init class behavior
- DS init path is different from legacy `admin.dropdown.js`
- DS search and selection behavior differs from legacy
- No confirmed React DS dropdown component import was found in this repo

---

## Migration Strategy

### Recommended overall approach

1. Document gaps and unsupported patterns first
2. Migrate isolated direct Twig include sites first
3. Introduce JS compatibility before form theme migration
4. Migrate React full dropdown separately from Twig
5. Treat `simple-dropdown` as a separate, potentially deferred subtrack
6. Update Behat selectors in the same phase as affected markup

### Recommended compatibility decision

For Twig rollout beyond isolated pages, introduce a compatibility layer that lets admin-ui scripts keep working while DS markup is adopted.

That layer should address:

- instance lookup differences
- selection API differences
- disabled / invalid state handling
- custom init semantics
- mutation-driven rebuild needs where still required

---

## Twig Migration Phases

### Phase 1: Isolated direct include sites - LOW RISK

Migrate direct dropdown includes first:

1. `src/bundle/Resources/views/themes/admin/content/tab/content.html.twig`
2. `src/bundle/Resources/views/themes/admin/content_type/tab/view.html.twig`

**Why first:**
- small scope
- no Symfony form theme blast radius
- easier manual verification
- helps prove DS single-select viability

**Checks:**
- selected value display
- language switching still works
- page-level styles still apply
- Behat locator updates if needed

---

### Phase 2: Twig compatibility bridge - HIGH PRIORITY

Before migrating form-theme dropdowns, define how legacy admin JS works with DS dropdowns.

Options:

**Option A - Recommended**
Create an admin-ui bridge that wraps DS dropdown instance behavior and exposes legacy-compatible methods used by existing JS.

**Option B**
Rewrite every JS consumer to use DS-native instance behavior and selectors before broader Twig rollout.

**Recommendation:** Option A first, then gradually simplify later.

---

### Phase 3: Form theme dropdown widget - HIGH RISK

Migrate:

- `src/bundle/Resources/views/themes/admin/ui/form_fields/dropdown_widget.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

**Notes:**
- this affects many collapsed Symfony choice widgets
- should happen only after Phase 2 compatibility is proven
- update related validators and filter scripts in same phase

---

### Phase 4: Advanced Twig feature parity review

Review legacy features that may not map directly:

- grouped items
- preferred choices
- select-all toggler
- custom item templates
- item icons
- selected item icons
- dynamic option recreation
- no-results empty state
- overflow counter for multi-select

**Rule:** unsupported patterns must be explicitly marked as:
- supported via wrapper
- redesigned
- deferred
- kept legacy temporarily

---

## React Migration Phases

### Phase R1: Full dropdown (`common/dropdown`) - MEDIUM/HIGH RISK

File:
- `src/bundle/ui-dev/src/modules/common/dropdown/dropdown.js`

Consumers:
- `src/bundle/ui-dev/src/modules/universal-discovery/components/filters/filters.js`
- `src/bundle/ui-dev/src/modules/universal-discovery/components/content-create-widget/content.create.widget.js`

**Recommended approach:**
Replace legacy React markup/behavior with a local admin-ui DS-aligned wrapper using `ids-dropdown*` conventions.

**Why not a direct import:**
No confirmed `@ids-components/components/Dropdown` React component was found in the available codebase.

**Must preserve:**
- single-select behavior
- portal / popup positioning behavior where required
- search behavior
- selected item rendering
- disabled state
- current UDW interactions

---

### Phase R2: Simple dropdown (`common/simple-dropdown`) - SEPARATE TRACK

File:
- `src/bundle/ui-dev/src/modules/common/simple-dropdown/simple.dropdown.js`

Consumers:
- `src/bundle/ui-dev/src/modules/universal-discovery/components/view-switcher/view.switcher.js`
- `src/bundle/ui-dev/src/modules/universal-discovery/components/sort-switcher/sort.switcher.js`
- `src/bundle/ui-dev/src/modules/sub-items/components/view-switcher/view.switcher.component.js`

**Status:** investigate whether to:
- migrate to a lightweight DS-based variant, or
- defer until a clear DS-equivalent interaction exists

**Reason:** current `simple-dropdown` is closer to a switcher/menu than the full form dropdown.

**Recommended plan status:** keep as a dedicated later phase, not part of the first Twig/full-dropdown rollout.

---

## JavaScript Migration Checklist

### Core files

- [ ] Review `src/bundle/Resources/public/js/scripts/core/dropdown.js`
- [ ] Decide whether to replace, wrap, or partially retain legacy instance logic
- [ ] Review `src/bundle/Resources/public/js/scripts/admin.dropdown.js`
- [ ] Review dynamic initialization in `src/bundle/Resources/public/js/scripts/admin.contenttype.edit.js`

### Consumer files

- [ ] `src/bundle/Resources/public/js/scripts/filters.action.btns.js`
- [ ] `src/bundle/Resources/public/js/scripts/admin.notifications.filters.js`
- [ ] `src/bundle/Resources/public/js/scripts/sidebar/extra.actions.js`
- [ ] `src/bundle/Resources/public/js/scripts/fieldType/ibexa_selection.js`
- [ ] `src/bundle/Resources/public/js/scripts/fieldType/ibexa_country.js`
- [ ] `src/bundle/Resources/public/js/scripts/admin.location.add.custom_url.js`

### Legacy selector/API assumptions to replace

- [ ] `.ibexa-dropdown`
- [ ] `.ibexa-dropdown__selection-info`
- [ ] `.ibexa-dropdown__source`
- [ ] `.ibexa-dropdown__item`
- [ ] `.ibexa-dropdown__remove-selection`
- [ ] `.ibexa-dropdown--disabled`
- [ ] `.ibexa-dropdown--expanded`
- [ ] `ibexaInstance`
- [ ] `selectOption()`
- [ ] `selectFirstOption()`
- [ ] `clearCurrentSelection()`
- [ ] `getSelectedItems()`
- [ ] `canSelectOnlyOne`

---

## SCSS Migration Checklist

### Core styles

- [ ] replace or retire legacy root styles in `src/bundle/Resources/public/scss/_dropdown.scss`
- [ ] review whether `src/bundle/Resources/public/scss/_dropdown-popover.scss` is still needed after DS migration
- [ ] review `src/bundle/Resources/public/scss/ui/modules/common/_simple-dropdown.scss`

### Context overrides

Update context selectors where DS markup replaces legacy markup, especially in:

- [ ] UDW dropdown contexts
- [ ] view switchers
- [ ] sub-items list
- [ ] filter/search forms
- [ ] preview/header contexts
- [ ] field edit contexts

**Rule:** if a template changes to DS markup, update context SCSS in the same phase.

---

## Behat / Test Impact

### Known direct selector impact

Update locators that target legacy dropdown classes, including but not limited to:

- `src/lib/Behat/Component/IbexaDropdown.php`
- `src/lib/Behat/Page/RoleUpdatePage.php`
- `src/lib/Behat/Page/ContentViewPage.php`
- `src/lib/Behat/Component/CreateNewPopup.php`
- `src/lib/Behat/Component/Fields/Selection.php`
- `src/lib/Behat/Component/Fields/Country.php`

### Rule

Do not leave migrated pages using DS dropdown markup while Behat still targets `ibexa-dropdown*`.

---

## Known Gaps / Blockers

### Blocker 1: No confirmed React DS dropdown component

No repository evidence was found for a ready-to-import React dropdown component under `@ids-components/components/Dropdown`.

**Action:** React migration should use a local DS wrapper or remain partially deferred.

---

### Blocker 2: Legacy Twig feature superset

The current DS Twig dropdown does not clearly cover all legacy features.

Potentially unsupported or requiring extra work:

- grouped items
- preferred choices
- select-all toggler
- custom templates
- icons
- dynamic option mutation handling
- custom no-results UI
- multi overflow counter

---

### Blocker 3: Validation and invalid-state wiring

Legacy validators target `.ibexa-dropdown__selection-info`.

Affected files include:

- `src/bundle/Resources/public/js/scripts/fieldType/ibexa_selection.js`
- `src/bundle/Resources/public/js/scripts/fieldType/ibexa_country.js`

**Action:** invalid state strategy must be defined before form-theme rollout.

---

### Blocker 4: Dynamic field insertion

`src/bundle/Resources/public/js/scripts/admin.contenttype.edit.js` manually initializes dropdowns in inserted field definition nodes.

**Action:** ensure DS init or compatibility init works for dynamically added markup.

---

## Validation Commands

```bash
# Twig lint
php bin/console lint:twig src/bundle/Resources/views/themes/admin/[path-to-file]

# PHP tests
composer test

# Code style
composer check-cs

# Static analysis
composer phpstan

# Frontend checks
yarn test
```

---

## Testing Strategy

### Required manual checks

**Twig direct-include pages**
- content preview language dropdown
- content type view language dropdown

**Symfony form-theme pages**
- single select widgets
- multi select widgets
- required field validation
- invalid state rendering
- disabled state rendering

**Functional JS flows**
- adaptive filters clear/apply
- notifications filters
- extra actions form state restore
- dynamic field insertion in content type edit
- custom URL modal behavior

**React**
- UDW filters
- create-content language dropdown
- UDW sort/view switchers
- sub-items view switcher

### Common issues to watch

1. DS dropdown initialized but legacy JS cannot control it
2. Behat locators still target old classes
3. invalid state stops highlighting dropdown properly
4. dynamic field insertion creates uninitialized dropdowns
5. multi-select behavior regresses due to overflow / selected-item rendering differences
6. grouped or preferred choices lose structure

---

## Recommended Execution Order

1. Create branch `IBX-11413-use-DS-dropdown`
2. Implement and validate plan document
3. Migrate isolated Twig direct include cases
4. Add/validate JS compatibility bridge
5. Migrate form theme dropdown widget
6. Update dependent JS + Behat + SCSS
7. Migrate React `common/dropdown`
8. Review `common/simple-dropdown` as separate phase
9. Run full verification

---

## Quick Reference

### Twig - initial migration targets

- `src/bundle/Resources/views/themes/admin/content/tab/content.html.twig`
- `src/bundle/Resources/views/themes/admin/content_type/tab/view.html.twig`

### Twig - high-risk shared targets

- `src/bundle/Resources/views/themes/admin/ui/form_fields/dropdown_widget.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

### React - full dropdown

- `src/bundle/ui-dev/src/modules/common/dropdown/dropdown.js`

### React - simple dropdown

- `src/bundle/ui-dev/src/modules/common/simple-dropdown/simple.dropdown.js`

### Core decision

**Do not treat dropdown migration as a simple component rename.**
It requires coordinated updates across Twig, JS, React, SCSS, and Behat.
