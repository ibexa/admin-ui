# RADIO BUTTON MIGRATION PLAN: Legacy Radio Patterns -> design-system Components

**Scope**: Cross-repository rollout across `vendor/ibexa/*`  
**Base Repository**: `ibexa/admin-ui`  
**Ticket / Branch**: `IBX-11506-use-ds-checkbox-component`  
**Date Created**: March 25, 2026  
**Status**: Planning

---

## ⚠️ CRITICAL DIFFERENCES FROM CHECKBOX MIGRATION

### Difference 1: `radio_button` Exists, but Adoption Is Not Wired Yet

The design-system Twig radio components already exist in:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/radio_button/input.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/radio_button/field.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/radio_button/list_field.html.twig`

Supporting Twig component classes also exist in:

- `vendor/ibexa/design-system-twig/src/lib/Twig/Components/RadioButton/Input.php`
- `vendor/ibexa/design-system-twig/src/lib/Twig/Components/RadioButton/Field.php`
- `vendor/ibexa/design-system-twig/src/lib/Twig/Components/RadioButton/ListField.php`

However, current downstream adoption is effectively absent, and standard radio initialization is not registered in:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

**Impact:** before broad rollout, confirm whether standard `radio_button` needs JS init at all and, if yes, wire it before downstream migration.

---

### Difference 2: Symfony Radio Rendering Shares the Checkbox Label Pipeline

Legacy radio rendering is still coupled to Symfony form theme behavior via:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

Relevant blocks:

- `radio_widget`
- `checkbox_radio_label`

These blocks inherit behavior from Symfony Bootstrap form themes, especially:

- `vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_5_layout.html.twig`
- `vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig`

**Impact:** radio migration must preserve the split widget/label contract unless a full replacement is intentionally designed and validated.

---

### Difference 3: Standard Radios and Tile Radios Must Be Split Into Separate Streams

Do not treat all radio UIs as one migration shape.

There are two distinct design-system paths:

- standard radios: `radio_button`
- tile/card radios: `alt_radio`

Relevant DS initialization already exists for alt-radio in:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

Relevant classes:

- `.ids-alt-radio`
- `.ids-alt-radio-list-field`

**Impact:** card-like or tile-like radio selectors should usually migrate to `alt_radio`, not `radio_button`.

---

### Difference 4: Many Legacy Radios Are Selector-Driven, Not Semantics-Driven

Legacy integrations frequently depend on classes and wrappers such as:

- `.ibexa-input--radio`
- `.ibexa-label--checkbox-radio`
- `.form-check`

These are used across Twig, JS, SCSS, and Behat selectors.

**Impact:** a radio migration is not complete when markup changes alone compile; coupled selectors must be migrated in the same interaction cluster.

---

## Overview

### Goals

1. Replace legacy radio markup and styling with design-system radio components where appropriate
2. Preserve functional parity for Symfony forms, expanded choices, filters, tables, and card selectors
3. Use a compatibility-first strategy for Symfony form theme radios
4. Use `alt_radio` for card/tile-style controls instead of forcing them into `radio_button`
5. Update JS, SCSS, and test selectors in the same commit as markup changes
6. Roll out the migration consistently across all affected `vendor/ibexa/*` repositories

### Non-Goals

1. Do not migrate checkbox-only behavior in this stream
2. Do not migrate toggle-style controls in this stream
3. Do not push or create PRs automatically

---

## Git Workflow

### Required Branch Naming

For every affected `vendor/ibexa/*` repository, create and use:

`IBX-11506-use-ds-checkbox-component`

### Branch Creation Rule

- create the branch from that repository's `main` branch
- do not branch from another feature branch
- keep all radio migration commits local on this branch
- do not push automatically

### Commit Scope Rule

- keep commits repository-local
- create one commit per changed file
- commit immediately after each validated file change
- if one file change requires coupled JS / SCSS / test updates in other files, still keep one commit per changed file
- migrate markup + JS + SCSS + tests together in execution order where selectors are coupled, but do not batch multiple changed files into a single commit

### Commit Message Prefix Rule

Use this prefix for every radio migration commit message:

`IBX-11507-use-ds-radio-button: `

Examples:

- `IBX-11507-use-ds-radio-button: use DS radio input in admin form theme`
- `IBX-11507-use-ds-radio-button: update radio selectors in product catalog`

---

## Target APIs

### Twig

Use the public radio component APIs from `ibexa/design-system-twig`:

- `<twig:ibexa:radio_button:input>`
- `<twig:ibexa:radio_button:field>`
- `<twig:ibexa:radio_button:list_field>`

Preferred syntax rule:

- use Twig component tag syntax directly: `<twig:ibexa:radio_button:input ... />`
- prefer `<twig:...>` over `{{ component('ibexa:radio_button:input', ...) }}` for this migration stream
- only use the `component()` function when tag syntax is genuinely awkward or impossible in a specific template context

Reference files:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/radio_button/input.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/radio_button/field.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/radio_button/list_field.html.twig`

### Alternative Radio UI

Use the alt-radio path for tile/card-style selection UIs:

- `.ids-alt-radio`
- `.ids-alt-radio-list-field`

Reference files:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/components/alt_radio/alt_radio_input.ts`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/components/alt_radio/alt_radios_list_field.ts`

### TypeScript

Standard radio TS classes currently exist in:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/components/radio_button/radio_button_input.ts`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/components/radio_button/radio_buttons_list_field.ts`

Before using them in downstream pages, confirm whether they require registration in:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

Also confirm that any required DS radio initialization is actually loaded by the consuming admin bundle, not only present in `design-system-twig` source.

---

## Migration Strategy

### Strategy A — Full DS Field in Symfony Form Theme

**Rejected for initial rollout.**

Reason:

- too risky for Symfony's split widget/label contract
- likely to cause double-label or wrapper regressions
- current downstream consumers still rely on `checkbox_radio_label`

### Strategy B — DS Input in Symfony Form Theme ✅

**Chosen strategy for form themes.**

Approach:

- keep `radio_widget` and `checkbox_radio_label` conceptually split
- migrate `radio_widget` toward `<twig:ibexa:radio_button:input>`
- keep `checkbox_radio_label` initially as a compatibility layer
- remove legacy classes and compatibility code gradually once downstream selectors are updated

### Strategy C — Full DS Components in Controlled Templates ✅

Use full DS field/list components in:

- hand-written Twig templates with local input + label markup
- repeated radio groups rendered from item arrays
- controlled views where wrapper and label structure are local

### Strategy D — Alt Radio for Tile/Card UIs ✅

Use the alt-radio path for:

- icon choice tiles
- visual cards with click-to-select behavior
- custom selected-state containers already acting like cards rather than standard radios

---

## Mapping Rules

### Rule 1: Symfony Form Themes Use Strategy B Only

For files like:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`
- downstream package form theme files

Use:

- `<twig:ibexa:radio_button:input>` in `radio_widget`
- compatibility-first `checkbox_radio_label`

Do **not** directly replace `checkbox_radio_label` with `<twig:ibexa:radio_button:field>`.

### Rule 2: Hand-Written Input + Label Pairs Use `radio_button:field`

Use `<twig:ibexa:radio_button:field>` when the radio and label are rendered together in local template code and there is no Symfony split-rendering dependency.

### Rule 3: Repeated Standard Radio Groups Use `radio_button:list_field`

Use `<twig:ibexa:radio_button:list_field>` when rendering a simple list of radios from items/config arrays.

### Rule 4: Tile/Card Radio Groups Use `alt_radio`

Use the alt-radio path when the visual design is a clickable card, tile, or icon selector with selected-state container styling.

### Rule 5: Migrate Interaction Clusters Together

When removing legacy radio classes/selectors, update together:

- Twig / JSX / TSX
- JS / TS logic
- SCSS
- Behat or UI test selectors

### Rule 6: Keep Temporary Compatibility Only When Needed

Compatibility selectors/classes may be retained temporarily when required for staged rollout, but should be removed in follow-up cleanup.

---

## Foundation Analysis

### Admin UI Chokepoints

The main migration chokepoints are:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`
- `src/bundle/Resources/public/js/scripts/button.state.radio.toggle.js`
- `src/bundle/Resources/public/scss/_extra-actions.scss`
- `src/bundle/Resources/public/scss/_instant-filter.scss`
- `src/bundle/Resources/public/scss/ui/modules/common/_popup.scss`
- `src/lib/Behat/Component/CreateNewPopup.php`

### Symfony Base Theme Constraints

Important inherited behavior lives in:

- `vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_5_layout.html.twig`
- `vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig`

Notable behaviors:

- `radio_widget` still relies on Symfony widget rendering contracts
- `checkbox_radio_label` renders the label around a pre-rendered widget flow
- required and inline classes are merged into the label block

### DS Initialization Constraint

Before migrating a page to standard DS radio components, verify whether the page needs design-system component initialization from:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

Current finding:

- alt-radio is registered there
- standard `radio_button` is not currently registered there

---

## Cross-Repository Inventory

### Phase 0 — Foundation / Shared Infra

1. `ibexa/design-system-twig`
2. `ibexa/admin-ui`

### Phase 1 — Major Form Theme Consumers

3. `ibexa/page-builder`
4. `ibexa/personalization`
5. `ibexa/storefront`

### Phase 2 — Custom Radio UI Consumers

6. `ibexa/discounts`
7. `ibexa/connect`
8. `ibexa/scheduler`

### Phase 3 — React / JS Consumers

9. `ibexa/product-catalog`
10. `ibexa/image-picker`
11. `ibexa/content-tree`
12. `ibexa/segmentation`

### Phase 4 — Tail Cleanup

13. `ibexa/corporate-account`
14. `ibexa/site-context`

---

## Package Categories

### 1. Foundation / Shared Infra

- `design-system-twig`
- `admin-ui`

### 2. Packages with Custom Form Themes

- `page-builder`
- `personalization`
- `storefront`

### 3. Packages with Hand-Written Radio Markup

- `admin-ui`
- `discounts`
- `connect`
- `scheduler`

### 4. Packages with Radio-Driven JS / React Logic

- `admin-ui`
- `product-catalog`
- `image-picker`
- `content-tree`
- `segmentation`
- `page-builder`

### 5. Packages with Behat / Selector Coupling

- `admin-ui`
- `discounts`

---

## design-system-twig Execution Plan

### Phase A: Foundation Audit

1. confirm public `radio_button` component API stability
2. review `ListField` validation gap in `src/lib/Twig/Components/RadioButton/ListField.php`
3. determine whether standard radios need JS init registration in `init_components.ts`
4. verify integration tests cover wrapper, input, and list markup expectations

### Phase B: Foundation Hardening

Required work:

- add or confirm standard radio bootstrap coverage if runtime init is needed
- keep class names stable: `.ids-radio-button`, `.ids-radio-button-field`, `.ids-radio-buttons-list-field`
- extend tests if API or bootstrap behavior changes

---

## Admin UI Execution Plan

### Phase A: Form Theme Foundation

Target file:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

Required work:

- prototype DS radio input usage in `radio_widget`
- review `checkbox_radio_label` compatibility for radio consumers
- verify expanded choice rendering still works in inherited paths

### Phase B: Low-Risk Standalone Radios

Start with isolated template radios that are not tightly coupled to card-style interactions.

Likely examples:

- `src/bundle/Resources/views/themes/admin/content/tab/locations/tab.html.twig`
- `src/bundle/Resources/views/themes/admin/content/tab/translations/tab.html.twig`
- `src/bundle/Resources/views/themes/admin/content/translation_add_form_fields.html.twig`

### Phase C: Dynamic Filter / Popup Clusters

Migrate radio interactions together in:

- instant filters
- popup selectors
- state-toggling buttons and auxiliary radio UI scripts

### Phase D: Cleanup

- remove temporary compatibility selectors/classes
- standardize on DS radio markup/classes

---

## Per-Repository Guidance

### `ibexa/page-builder`

Focus areas:

- `src/bundle/Resources/views/page_builder/block/form_fields.html.twig`
- scheduler and reveal/hide configuration radios
- layout selector and any card-like radios that may fit `alt_radio`

### `ibexa/discounts`

Focus areas:

- `src/bundle/Resources/views/themes/admin/discounts/extra_actions_form_fields.html.twig`
- selected-state radio cards and icons
- related JS and SCSS for selected option containers

### `ibexa/storefront`

Focus areas:

- `src/bundle/Resources/views/themes/storefront/storefront/form_fields.html.twig`
- custom `choice_widget_expanded` rendering

### `ibexa/connect`

Focus areas:

- hand-written radio option markup
- action-edit radio toggles

### `ibexa/scheduler`

Focus areas:

- custom visibility/schedule selection radios
- any legacy wrappers or label selectors

### React / JS Consumers

Packages such as `product-catalog`, `image-picker`, `content-tree`, and `segmentation` should be migrated after the `admin-ui` form-theme and selector rules stabilize.

---

## Validation Commands

### Twig Syntax

```bash
php bin/console lint:twig src/bundle/Resources/views/themes/admin/[file-path]
```

### PHP / General Validation

```bash
composer test
composer check-cs
composer phpstan
```

### Frontend Validation

```bash
yarn test
```

---

## Required Testing Strategy

### 1. Template / Runtime Validation

- lint Twig syntax
- clear cache when needed
- verify the page loads without exceptions

### 2. Form Theme Validation

For each migrated form-theme path, verify:

- radio inputs render correctly inside expanded choices
- labels still bind correctly to the correct input id
- no double-label rendering appears

### 3. DS Initialization Validation

For each migrated page, verify whether standard DS radio JS init is required and present.

For alt-radio pages, verify:

- `.ids-alt-radio` components initialize automatically
- `.ids-alt-radio-list-field` change events work

### 4. State Validation

Verify all relevant states:

- unchecked
- checked
- disabled
- required
- error
- selected tile/card state where applicable

### 5. Interaction Validation

Verify behavior for:

- expanded choice groups
- filter counters and panel switching
- card/tile selection
- conditional subfield visibility

### 6. Selector Validation

Update and validate any coupled selectors in:

- JS / TS
- SCSS
- Behat / UI tests

---

## Common Risks

### Risk 1: Double Label Rendering

Problem:

- using `<twig:ibexa:radio_button:field>` inside Symfony's split radio rendering path

Fix:

- keep Strategy B for form themes

### Risk 2: Wrong Component Family Chosen

Problem:

- card/tile selectors are migrated to standard `radio_button` when they actually need `alt_radio`

Fix:

- classify each radio cluster before changing markup

### Risk 3: Hidden Selector Coupling

Problem:

- JS, SCSS, or Behat still targets `.ibexa-input--radio` or `.ibexa-label--checkbox-radio`

Fix:

- migrate markup, JS, SCSS, and tests together

### Risk 4: Unwired Standard Radio Bootstrap

Problem:

- standard radio components are migrated assuming DS bootstrap exists when `init_components.ts` does not register them

Fix:

- decide bootstrap requirements in `design-system-twig` before downstream rollout

### Risk 5: Storefront Drift

Problem:

- admin-oriented migration assumptions are applied directly to storefront expanded-choice rendering

Fix:

- treat storefront as a separate form-theme stream

---

## Per-Commit Checklist

Before each commit:

- [ ] confirm repository branch is `IBX-11507-use-ds-radio-button-component`
- [ ] confirm branch was created from `main`
- [ ] read the full interaction cluster being migrated
- [ ] identify coupled Twig / React / JS / SCSS / test selectors
- [ ] classify the target as `radio_button` or `alt_radio`
- [ ] confirm whether DS init coverage exists for the target page

During each commit:

- [ ] migrate markup to the correct DS radio API for the context
- [ ] update coupled JS / TS selectors and logic
- [ ] update coupled SCSS selectors and states
- [ ] update Behat / UI locators where applicable
- [ ] keep temporary compatibility only when necessary

After each commit:

- [ ] lint Twig syntax
- [ ] run relevant tests/checks
- [ ] manually verify runtime behavior
- [ ] ensure no unrelated changes are included

---

## Execution Model

- one logical change at a time
- one commit per change on the proper repository branch
- local changes only
- no automatic pushes
- prefer small, reviewable commits

---

## Follow-Up Streams

Once radio migration is stable, plan follow-up streams for:

1. checkbox compatibility selector cleanup
2. alt-radio adoption cleanup
3. toggle migration where controls are semantically not radios
4. final CSS de-legacy pass

---

**Plan Status**: Planning  
**Plan Version**: 1.0  
**Date**: March 25, 2026  
**Next Step**: harden `design-system-twig` radio foundations, then implement `admin-ui` form-theme migration on `IBX-11507-use-ds-radio-button-component`
