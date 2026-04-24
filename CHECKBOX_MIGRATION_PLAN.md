# CHECKBOX MIGRATION PLAN: Legacy Checkbox/Radio Patterns -> design-system Components

**Scope**: Cross-repository rollout across `vendor/ibexa/*`  
**Base Repository**: `ibexa/admin-ui`  
**Ticket / Branch**: `IBX-11506-use-ds-checkbox-component`  
**Date Created**: March 24, 2026  
**Status**: Planning

---

## ⚠️ CRITICAL DIFFERENCES FROM BUTTON / LABEL MIGRATION

### Difference 1: Checkboxes Are Coupled to Symfony Form Theme Rendering

Checkbox migration is not a simple class swap.

Primary foundation file:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

Relevant blocks:

- `checkbox_widget`
- `checkbox_radio_label`

These blocks inherit behavior from Symfony Bootstrap form themes, especially:

- `vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_5_layout.html.twig`
- `vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig`

**Impact:** legacy checkbox migration must preserve Symfony's widget/label pipeline unless we intentionally replace it.

---

### Difference 2: Strategy B Is Required for Form Theme Migration

For Symfony form themes, use **Strategy B**:

- keep the split widget/label rendering model
- migrate `checkbox_widget` toward `<twig:ibexa:checkbox:input>`
- keep `checkbox_radio_label` as a compatibility layer initially

#### ✅ Recommended

```twig
{# form theme checkbox_widget target direction #}
<twig:ibexa:checkbox:input
    id="{{ id }}"
    name="{{ full_name }}"
    :checked="checked"
    :disabled="disabled"
    :required="required"
    value="{{ value }}"
    {{ block('widget_attributes') }}
/>
```

#### ❌ Do Not Use as a Drop-In in `checkbox_radio_label`

```twig
{# wrong migration shape for form theme split rendering #}
<twig:ibexa:checkbox:field ...>
```

**Why?** `<twig:ibexa:checkbox:field>` already renders both input and label, while Symfony's `checkbox_radio_label` expects to render the label around a pre-rendered widget.

---

### Difference 3: DS Checkbox JS Initialization Must Be Available

Twig checkbox components rely on design-system initialization from:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

Relevant initializers:

- `.ids-checkbox`
- `.ids-checkboxes-list-field`

**Impact:** pages migrated to DS checkbox components must load the design-system JS init path, especially for:

- indeterminate checkboxes
- checkbox list fields
- custom DS checkbox events

---

### Difference 4: Toggle-Style Controls Are Out of Scope

Do **not** mix toggle migration into this checkbox plan.

Examples to treat separately:

- controls already mapped to `toggle_widget`
- controls that semantically behave like toggle buttons rather than plain checkboxes

**Impact:** migrate only true checkbox semantics in this stream.

---

## Overview

### Goals

1. Replace legacy checkbox markup and styling with design-system checkbox components
2. Preserve functional parity for forms, filters, tables, and bulk selection
3. Use **Strategy B** for Symfony form theme checkboxes
4. Use full DS field/list components in hand-written Twig and React where structure is controlled
5. Update JS, SCSS, and test selectors in the same commit as markup changes
6. Roll out the migration consistently across all affected `vendor/ibexa/*` repositories

### Non-Goals

1. Do not migrate toggle-button controls in this stream
2. Do not run radio migration as part of this plan, except where radio and checkbox rules must be documented together
3. Do not push or create PRs automatically

---

## Git Workflow

### Required Branch Naming

For every affected `vendor/ibexa/*` repository, create and use:

`IBX-11506-use-ds-checkbox-component`

### Branch Creation Rule

- create the branch from that repository's `main` branch
- do not branch from another feature branch
- keep all checkbox migration commits local on this branch
- do not push automatically

### Commit Scope Rule

- keep commits repository-local
- create one commit per changed file
- do not batch multiple file migrations into a single commit, even within the same repository
- migrate markup + JS + SCSS + tests together where selectors are coupled

---

## Target APIs

### Twig

Use the public checkbox component APIs from `ibexa/design-system-twig`:

- `<twig:ibexa:checkbox:input>`
- `<twig:ibexa:checkbox:field>`
- `<twig:ibexa:checkbox:list_field>`

Preferred syntax rule:

- use Twig component tag syntax directly: `<twig:ibexa:checkbox:input ... />`
- prefer `<twig:...>` over `{{ component('ibexa:checkbox:input', ...) }}` for this migration stream
- only use the `component()` function when tag syntax is genuinely awkward or impossible in a specific template context

Reference files:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/checkbox/input.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/checkbox/field.html.twig`
- `vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/checkbox/list_field.html.twig`

### React

Use the public React checkbox component APIs from the design system package:

- `CheckboxInput`
- `CheckboxField`
- `CheckboxesListField`

Reference files:

- `../design-system/packages/components/src/components/Checkbox/CheckboxInput/CheckboxInput.tsx`
- `../design-system/packages/components/src/components/Checkbox/CheckboxField/CheckboxField.tsx`
- `../design-system/packages/components/src/components/Checkbox/CheckboxesListField/CheckboxesListField.tsx`

---

## Migration Strategy

### Strategy A — Full DS Field in Symfony Form Theme

**Rejected for initial rollout.**

Reason:

- too risky for Symfony's split widget/label contract
- likely to produce double-label or wrapper regressions
- relation and expanded-choice overrides depend on current block behavior

### Strategy B — DS Input in Symfony Form Theme ✅

**Chosen strategy for form themes.**

Approach:

- keep `checkbox_widget` and `checkbox_radio_label` conceptually split
- migrate `checkbox_widget` toward `<twig:ibexa:checkbox:input>`
- keep `checkbox_radio_label` initially as a compatibility layer
- remove legacy classes and compatibility code gradually once downstream selectors are updated

### Strategy C — Full DS Components in Controlled Templates ✅

Use full DS field/list components in:

- hand-written Twig templates with local input + label markup
- checkbox groups rendered from item arrays
- React views where the component tree is fully controlled

---

## Mapping Rules

### Rule 1: Symfony Form Themes Use Strategy B Only

For files like:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`
- downstream package form theme files

Use:

- `<twig:ibexa:checkbox:input>` in `checkbox_widget`
- compatibility-first `checkbox_radio_label`

Do **not** directly replace `checkbox_radio_label` with `<twig:ibexa:checkbox:field>`.

### Rule 2: Hand-Written Input + Label Pairs Use `checkbox:field`

Use `<twig:ibexa:checkbox:field>` when the checkbox and its label are rendered together in local template code and there is no Symfony split-rendering dependency.

### Rule 3: Repeated Checkbox Groups Use `checkbox:list_field`

Use `<twig:ibexa:checkbox:list_field>` when rendering a list of checkboxes from items/config arrays and when DS list-field events are useful.

### Rule 4: Bare Selection Cells Use `checkbox:input`

Use `<twig:ibexa:checkbox:input>` or React `CheckboxInput` for:

- table selection cells
- header bulk-select checkboxes
- bare checkbox controls with no local label element

### Rule 5: Migrate Interaction Clusters Together

When removing legacy checkbox classes/selectors, update together:

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
- `src/bundle/Resources/views/themes/admin/ui/component/table/table_head_cell.html.twig`
- `src/bundle/Resources/views/themes/admin/ui/component/table/table_body_cell.html.twig`
- `src/bundle/Resources/public/js/scripts/admin.table.js`
- `src/bundle/ui-dev/src/modules/common/dropdown/dropdown.js`
- `src/bundle/ui-dev/src/modules/universal-discovery/components/content-type-selector/content.type.selector.js`
- `src/bundle/ui-dev/src/modules/sub-items/components/three-state-checkbox/three.state.checkbox.component.js`

### Symfony Base Theme Constraints

Important inherited behavior lives in:

- `vendor/symfony/twig-bridge/Resources/views/Form/bootstrap_5_layout.html.twig`
- `vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig`

Notable behaviors:

- `checkbox_widget` injects `.form-check-input`
- `checkbox_widget` wraps controls in `.form-check`
- `checkbox_radio_label` prints widget first, then `<label>`
- required and inline classes are merged into the label block

### DS Initialization Constraint

Before migrating a page to DS checkbox components, verify that the page loads the design-system component initialization path from:

- `vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts`

---

## Cross-Repository Inventory

### Phase 0 — Foundation / Shared Infra

1. `ibexa/design-system-twig`
2. `ibexa/admin-ui-assets`
3. `ibexa/admin-ui`

### Phase 1 — Major Downstream Contracts

4. `ibexa/product-catalog`
5. `ibexa/page-builder`

### Phase 2 — Custom Form Theme Consumers

6. `ibexa/site-context`
7. `ibexa/site-factory`
8. `ibexa/storefront`
9. `ibexa/discounts`
10. `ibexa/personalization`

### Phase 3 — React-Heavy Consumers

11. `ibexa/image-picker`
12. `ibexa/content-tree`
13. `ibexa/calendar`
14. `ibexa/segmentation`

### Phase 4 — Bulk Selection / List Consumers

15. `ibexa/corporate-account`
16. `ibexa/scheduler`
17. `ibexa/connector-ai`
18. `ibexa/shipping`
19. `ibexa/fieldtype-page`
20. `ibexa/fieldtype-matrix`
21. `ibexa/share`

### Phase 5 — Tail Cleanup

22. `ibexa/connect`
23. `ibexa/activity-log`
24. `ibexa/order-management`

---

## Package Categories

### 1. Foundation / Shared Infra

- `admin-ui`
- `admin-ui-assets`
- `design-system-twig`

### 2. Packages with Custom Form Themes

- `product-catalog`
- `page-builder`
- `site-context`
- `site-factory`
- `storefront`
- `discounts`
- `personalization`

### 3. Packages with Table / List Bulk Selection

- `admin-ui`
- `product-catalog`
- `corporate-account`
- `scheduler`
- `segmentation`
- `connector-ai`
- `shipping`
- `fieldtype-page`
- `fieldtype-matrix`
- `share`

### 4. Packages with React Checkbox Usage

- `admin-ui`
- `product-catalog`
- `image-picker`
- `content-tree`
- `calendar`
- `segmentation`

### 5. Packages with Radio / Shared Choice Rules

- `admin-ui`
- `product-catalog`
- `discounts`
- `personalization`
- `scheduler`
- `image-picker`
- `connect`
- `site-context`
- `fieldtype-page`

---

## Admin UI Execution Plan

### Phase A: Foundation Design

1. Confirm DS JS/init loading coverage for pages that will receive DS checkboxes
2. Design the Strategy B implementation for `ui/form_fields.html.twig`
3. Define temporary compatibility selector policy for legacy JS/SCSS/tests
4. Confirm toggle controls remain excluded

### Phase B: Form Theme Foundation

Target file:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig`

Required work:

- prototype DS checkbox input usage in `checkbox_widget`
- review `checkbox_radio_label` compatibility
- verify relation/radio form theme overrides still work

### Phase C: Low-Risk Standalone Checkboxes

Start with isolated template or React checkbox controls that are not tightly coupled to shared table bulk-selection logic.

### Phase D: Dynamic Form / Filter Clusters

Migrate checkbox interactions together in:

- dropdown multi-selects
- content-type selectors
- relation editors
- notifications and search filters

### Phase E: Shared Table Bulk Selection

Migrate shared infrastructure together:

- table header checkbox markup
- table body checkbox markup
- indeterminate logic
- bulk action JS
- table checkbox SCSS

### Phase F: Cleanup

- remove temporary compatibility selectors/classes
- standardize on DS checkbox markup/classes

---

## Per-Repository Guidance

### `ibexa/product-catalog`

High-risk downstream consumer after `admin-ui`.

Focus areas:

- `src/bundle/Resources/views/themes/admin/product_catalog/form_fields.html.twig`
- product tables and variant tables
- data-grid checkbox selection
- React product selector / discovery widget tables
- choice and radio filter JS

### `ibexa/page-builder`

Focus areas:

- custom checkbox widget templates
- attribute-group checkbox JS and SCSS

### `ibexa/personalization`

Focus areas:

- custom form themes
- scenario edit JS
- checkbox/radio-dependent dashboard widgets

### `ibexa/image-picker`

Focus areas:

- React checkbox and radio filter groups
- filter SCSS
- selection toggler behavior

### `ibexa/site-context`, `ibexa/site-factory`, `ibexa/storefront`, `ibexa/discounts`

Focus areas:

- custom form theme overrides
- expanded choice / checkbox row markup
- filter and shopping-list selector logic

### Bulk Selection Consumers

Packages such as `corporate-account`, `scheduler`, `connector-ai`, `shipping`, `fieldtype-page`, `fieldtype-matrix`, and `share` should be migrated after `admin-ui` shared table checkbox rules stabilize.

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

### 2. DS Initialization Validation

For each migrated page, verify:

- DS checkbox JS initializes automatically
- `.ids-checkbox` components are active
- `.ids-checkboxes-list-field` events work where used

### 3. State Validation

Verify all relevant states:

- unchecked
- checked
- disabled
- required
- error
- indeterminate

### 4. Interaction Validation

Verify behavior for:

- bulk selection
- filter counters
- dropdown multi-selects
- relation editors
- dynamic subfield visibility

### 5. Selector Validation

Update and validate any coupled selectors in:

- JS / TS
- SCSS
- Behat / UI tests

---

## Common Risks

### Risk 1: DS JS Not Loaded

Problem:

- DS checkbox markup renders, but init-dependent behavior is missing

Fix:

- verify the page includes the DS component initialization path from `init_components.ts`

### Risk 2: Double Label Rendering

Problem:

- using `<twig:ibexa:checkbox:field>` inside Symfony's split checkbox rendering path

Fix:

- keep Strategy B for form themes

### Risk 3: Broken Table Bulk Selection

Problem:

- table JS still targets legacy checkbox selectors after markup migration

Fix:

- migrate table markup, JS, SCSS, and tests together

### Risk 4: Toggle Controls Accidentally Included

Problem:

- controls with toggle semantics get migrated as checkboxes

Fix:

- explicitly keep toggle migration out of scope for this stream

### Risk 5: Downstream Package Drift

Problem:

- downstream repos continue depending on old `admin-ui` checkbox structure

Fix:

- migrate repos in the order defined in this plan

---

## Per-Commit Checklist

Before each commit:

- [ ] confirm repository branch is `IBX-11506-use-ds-checkbox-component`
- [ ] confirm branch was created from `main`
- [ ] read the full interaction cluster being migrated
- [ ] identify coupled Twig / React / JS / SCSS / test selectors
- [ ] confirm whether DS init coverage exists for the target page

During each commit:

- [ ] confirm the commit contains exactly one changed file
- [ ] migrate markup to DS checkbox API appropriate for the context
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

- one interaction cluster at a time
- one repository at a time
- local changes only
- no automatic pushes
- prefer small, reviewable commits

---

## Follow-Up Streams

Once checkbox migration is stable, plan follow-up streams for:

1. radio migration
2. toggle migration
3. compatibility selector cleanup
4. final CSS de-legacy pass

---

**Plan Status**: Planning  
**Plan Version**: 1.0  
**Date**: March 24, 2026  
**Next Step**: implement `admin-ui` foundation on `IBX-11506-use-ds-checkbox-component` from `main`
