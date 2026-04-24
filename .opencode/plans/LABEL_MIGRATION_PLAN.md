# 📋 LABEL MIGRATION PLAN: Legacy ibexa-label → design-system Components

**Package**: `ibexa/admin-ui`  
**Date Created**: March 6, 2026  
**Status**: In Progress

> Update as of March 16, 2026: label migration work is already in progress across `admin-ui` and related `vendor/ibexa/*` repositories on the `IBX-11236-use-ds-label-component` branch. This document started as an `admin-ui`-only plan and now also serves as the source of truth for migration rules used across package repos.

---

## ⚠️ CRITICAL DIFFERENCES FROM BUTTON MIGRATION - READ FIRST

### Difference 1: CSS Class Name Changes

Unlike buttons where classes stay as `ibexa-btn*`, labels completely rename:

| Legacy | Design System |
|--------|--------------|
| `ibexa-label` | `ids-label` |
| `ibexa-label--required` (via `.required` co-class) | `ids-label--required` (via `required` prop) |
| `ibexa-label--error` (via `.is-invalid` state) | `ids-label--error` (via `error` prop) |

**Impact:** Any JavaScript files using `.querySelector('.ibexa-label')` or `.classList` manipulation must be updated alongside each template migration.

---

### Difference 2: `required` Is Now a Prop, Not a Co-Class

Legacy labels used a `.required` co-class:

```twig
❌ BEFORE:
<label class="ibexa-label required">URL</label>
```

Design system label uses a `required` boolean prop:

```twig
✅ AFTER:
<twig:ibexa:label :required="true">URL</twig:ibexa:label>
```

---

### Difference 3: `error` Is Now a Prop, Not an External State Class

Legacy validation turned labels red by toggling `.is-invalid` via JavaScript on the `<label>`:

```js
❌ BEFORE:
label.classList.toggle('is-invalid', !isValid);
// CSS rule: .ibexa-label.form-label.is-invalid { color: $ibexa-color-danger; }
```

Design system label uses an `error` boolean prop — this state must be driven at render time or via React state. For vanilla JS contexts, toggle `ids-label--error` directly:

```twig
✅ TWIG: <twig:ibexa:label :error="field_has_error">...</twig:ibexa:label>
```
```jsx
✅ REACT: <Label error={hasError}>...</Label>
```
```js
✅ VANILLA JS: label.classList.toggle('ids-label--error', !isValid);
```

---

### Difference 4: `for` Attribute Unchanged in Twig, `htmlFor` in React

In Twig: `for="field-id"` is passed directly as a prop (same as HTML attribute).  
In React: use `htmlFor="field-id"` (React standard, already supported by the `Label` component).

---

## ⚠️ OUT OF SCOPE (Document Only)

### Unmapped Variants — Keep as Legacy + Comment

These `ibexa-label` BEM modifiers have **no equivalent in the design system label component**:

| Legacy Class | Reason for Exclusion | Action |
|---|---|---|
| `ibexa-label--checkbox-radio` | Will be migrated as part of a separate Checkbox/Radio component migration | **SKIP — do not migrate now** |
| `ibexa-label--active` | Dynamic state toggled by JS (language selector); no design system equivalent | **Add TODO comment** |
| `ibexa-label--small` | No dedicated DS size modifier exists; preserve class when needed on `<twig:ibexa:label>` | **Migrate to component and keep class if required** |

```twig
{# TODO: Migrate to design-system-twig when ibexa-label--active state has a design system equivalent #}
<label class="ibexa-label ibexa-label--active">...</label>
```

---

### Symfony Form Theme Labels — Investigate Custom Approach

Files that inject `ibexa-label` via Symfony form theme `label_attr` merge cannot use `<twig:ibexa:label>` directly:

- `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig` (form_label block only — NOT checkbox_radio_label)
- `src/bundle/Resources/views/themes/admin/content/form_fields.html.twig`
- `src/bundle/Resources/views/themes/admin/account/change_password/form_fields.html.twig`

**Required investigation (Phase 0)**: Determine whether a custom Symfony form theme block can call `<twig:ibexa:label>` to render the label element itself. If not feasible, the fallback is to change the injected class from `ibexa-label` to `ids-label` directly in `label_attr`.

> The `checkbox_radio_label` block in `ui/form_fields.html.twig` is **excluded** from this migration (handled with checkbox/radio component migration).

---

## Table of Contents

1. [Overview](#overview)
2. [Migration Statistics](#migration-statistics)
3. [Migration Patterns](#migration-patterns)
4. [State/Prop Mapping Reference](#stateprop-mapping-reference)
5. [Migration Order by Priority](#migration-order-by-priority)
6. [Per-File Migration Checklist](#per-file-migration-checklist)
7. [JavaScript Selector Updates](#javascript-selector-updates)
8. [Special Cases & Warnings](#special-cases--warnings)
9. [Style Guide Compliance](#style-guide-compliance)
10. [Validation Commands](#validation-commands)
11. [Testing Strategy](#testing-strategy)
12. [Progress Tracking](#progress-tracking)

---

## Overview

### Goals

1. **Replace `<label class="ibexa-label">` patterns** with `<twig:ibexa:label>` (Twig) and `<Label>` from `@ids-components/components/Label` (React)
2. **Maintain 100% visual and functional parity**
3. **Update JavaScript selectors** that target `.ibexa-label` to target `.ids-label`
4. **Investigate Symfony form theme** approach for form_fields files
5. **Document unmapped patterns** with TODO comments
6. **Follow Ibexa Twig and React style guides** throughout

### Approach

- **One file at a time** with individual commits
- **Update JS selectors in the same commit** as the corresponding template migration
- **Manual functional testing** per file
- **Git workflow**: LOCAL CHANGES ONLY (no automatic pushes)

---

## Migration Statistics

### Files Overview

**Twig Templates (direct `<label class="ibexa-label">`)**: 17 files, ~34 label instances

| Category | Files | Labels |
|---|---|---|
| Field type edit | 5 files | 10 labels |
| Modals | 3 files | 9 labels |
| Search/filters | 2 files | 8 labels |
| Content widgets | 2 files | 2 labels |
| Login page | 1 file | 2 labels |
| UI components | 2 files | 2 labels |
| Trash | 1 file | 1 label |

**Twig Templates (form theme — `label_attr` injection)**: 3 files — _under investigation_

**React/JavaScript Files**: 2 files, 3 label instances
- `content.create.widget.js`: 2 (1 true `<label>` + 1 `<div>` misusing class — skip `<div>`)
- `instant.filter.component.js`: 1 (true `<label>`)

**JavaScript Files (DOM selector only — no template)**: 3 files
- `location.edit.js`: selectors + `.ibexa-label--active` toggle
- `form.validation.helper.js`: selector + `.is-invalid` toggle
- `admin.contenttype.edit.js`: selector only

### Label State Distribution

- **Plain labels** (no state): ~20 instances
- **Required labels** (`.required` co-class): 7 instances (url_wildcard ×4, modal_add_custom_url ×1, update ×2)
- **Error state** (`.is-invalid` via JS): driven by `form.validation.helper.js`
- **`--active` state** (excluded): 1 component
- **`--checkbox-radio` variant** (excluded): form_fields.html.twig + content.create.widget.js

---

## Migration Patterns

### Pattern 1: Simple Label (No State)

```twig
❌ BEFORE:
<label class="ibexa-label" for="username">{{ 'authentication.username'|trans|desc('Username') }}</label>

✅ AFTER:
<twig:ibexa:label for="username">
    {{ 'authentication.username'|trans|desc('Username') }}
</twig:ibexa:label>
```

### Pattern 2: Required Label

```twig
❌ BEFORE:
<label class="ibexa-label required">{{ 'tab.urls.add.path'|trans|desc('URL') }}</label>

✅ AFTER:
<twig:ibexa:label :required="true">
    {{ 'tab.urls.add.path'|trans|desc('URL') }}
</twig:ibexa:label>
```

**Note:** The `.required` CSS class is replaced by the `required` boolean prop, which adds `ids-label--required` internally.

### Pattern 3: Label with Additional Context Classes

```twig
❌ BEFORE:
<label class="ibexa-label ibexa-modal__label required">
    {{ 'url_wildcard.modal.create.url.wildcard'|trans|desc('URL wildcard') }}
</label>

✅ AFTER:
<twig:ibexa:label class="ibexa-modal__label" :required="true">
    {{ 'url_wildcard.modal.create.url.wildcard'|trans|desc('URL wildcard') }}
</twig:ibexa:label>
```

**Note:** Context-specific classes (like `ibexa-modal__label`) are passed via the `class` prop and merged alongside `ids-label`.

### Pattern 4: Label with `for` Attribute and co-class

```twig
❌ BEFORE:
<label class="ibexa-label form-label" for="trash_search_content_type">
    {{ 'trash.search.content_type'|trans|desc('Content type') }}
</label>

✅ AFTER:
<twig:ibexa:label class="form-label" for="trash_search_content_type">
    {{ 'trash.search.content_type'|trans|desc('Content type') }}
</twig:ibexa:label>
```

### Pattern 5: Multiline Label with Children

```twig
❌ BEFORE:
<label class="ibexa-label ibexa-user-invitation-modal__label">
    {{ 'user.invitation.modal.email_address'|trans|desc('Email address') }}
</label>

✅ AFTER:
<twig:ibexa:label class="ibexa-user-invitation-modal__label">
    {{ 'user.invitation.modal.email_address'|trans|desc('Email address') }}
</twig:ibexa:label>
```

### Pattern 6: Label on Non-`<label>` Element (DEFAULT TO COMPONENT)

If a non-`<label>` element is used purely as visual label text, migrate it to `<twig:ibexa:label>` unless there is a strong semantic or accessibility reason not to.

```twig
<twig:ibexa:label class="ibexa-label--small ibexa-edit-header__action-name">
    ...
</twig:ibexa:label>
```

### Pattern 7: Empty Placeholder Label (KEEP AS LEGACY + COMMENT)

`adaptive_filters.html.twig` contains `<label class="ibexa-label"></label>` — an empty label used as a layout spacer.

```twig
{# TODO: Review if this empty label is still needed after design system migration #}
<label class="ibexa-label"></label>
```

### Pattern 8: Label Class Injected via Variable (Class-Name Swap Only)

`double_input_range.html.twig` builds a `single_label_attr` dict with `ibexa-label` prepended:

```twig
❌ BEFORE:
{% set single_label_attr = {
    class: ('ibexa-label ' ~ single_label_attr.class|default(''))|trim,
    ...
} %}
<label {{ attr(single_label_attr) }}>...</label>

✅ AFTER (replace class value only):
{% set single_label_attr = {
    class: ('ids-label ' ~ single_label_attr.class|default(''))|trim,
    ...
} %}
<label {{ attr(single_label_attr) }}>...</label>
```

> This file cannot use `<twig:ibexa:label>` because `single_label_attr` is passed externally as a data structure. Update the class name only.

---

## React Migration Patterns

### Pattern R1: True `<label>` Element with Active State

```jsx
❌ BEFORE (instant.filter.component.js):
import { createCssClassNames } from '../../../common/helpers/css.class.names';

const labelClassName = createCssClassNames({
    'form-check-label': true,
    'ibexa-label': true,
    'ibexa-label--active': activeLanguage === item.value,
});
<label className={labelClassName} htmlFor={radioId}>
    {item.label}
</label>

✅ AFTER:
import { Label } from '@ids-components/components/Label';

// TODO: Migrate ibexa-label--active to design system equivalent when available
const extraClassName = createCssClassNames({
    'form-check-label': true,
    'ibexa-label--active': activeLanguage === item.value,
});
<Label className={extraClassName} htmlFor={radioId}>
    {item.label}
</Label>
```

### Pattern R2: True `<label>` Element with Context Class

```jsx
❌ BEFORE (content.create.widget.js, line 190):
<label className="ibexa-label ibexa-extra-actions__section-header">{selectLanguageLabel}</label>

✅ AFTER:
import { Label } from '@ids-components/components/Label';

<Label className="ibexa-extra-actions__section-header">{selectLanguageLabel}</Label>
```

### Pattern R3: `<div>` Misusing `ibexa-label--checkbox-radio` (KEEP AS LEGACY)

```jsx
{/* SKIP: ibexa-label--checkbox-radio will be migrated with the Checkbox/Radio component migration */}
{/* Legacy: <div> using ibexa-label class — kept as-is */}
<div className="ibexa-label ibexa-label--checkbox-radio form-check-label">
    {name}
</div>
```

---

## State/Prop Mapping Reference

### Label State Mapping

| Legacy Pattern | design-system-twig prop | React prop | Notes |
|---|---|---|---|
| `class="ibexa-label"` | _(no props needed)_ | _(no props needed)_ | Base class auto-applied |
| `class="ibexa-label required"` | `:required="true"` | `required={true}` | `.required` co-class → prop |
| `.is-invalid` toggled by JS | `:error="..."` or JS sets `ids-label--error` | `error={hasError}` | See JS selector updates |
| `ibexa-label--checkbox-radio` | **SKIP** | **SKIP** | With checkbox/radio migration |
| `ibexa-label--active` | **TODO comment** | **TODO comment** | No DS equivalent yet |
| `ibexa-label--small` | **TODO comment** | **TODO comment** | Only on `<div>`, not `<label>` |

### Extra Classes Mapping

| Legacy Pattern | Twig Component | Notes |
|---|---|---|
| `ibexa-label form-label` | `class="form-label"` | `form-label` passed via `class` prop |
| `ibexa-label ibexa-modal__label` | `class="ibexa-modal__label"` | Context class preserved |
| `ibexa-label ibexa-extra-actions__section-header` | `class="ibexa-extra-actions__section-header"` | Context class preserved |
| `ibexa-label ibexa-user-invitation-modal__label` | `class="ibexa-user-invitation-modal__label"` | Context class preserved |

---

## Migration Order by Priority

### Phase 0: Symfony Form Theme Investigation ⚠️ RECOMMENDED FIRST

**Investigate whether Symfony form theme blocks can render `<twig:ibexa:label>`:**

```
Files: 3 form_fields.html.twig files
Outcome A: Override form_label block to render <twig:ibexa:label>
Outcome B: Simple class name swap — ibexa-label → ids-label in label_attr
```

This phase does **not** block Phase 1–3 but its outcome informs the form theme files.

---

### Phase 1: Simple Labels (8 files) — HIGH PRIORITY ⭐

Start here — low risk, no dynamic states.

#### 1.1 Login & Account (2 files)

1. `src/bundle/Resources/views/themes/admin/account/login/index.html.twig` — 2 labels (with `for`)
2. `src/bundle/Resources/views/themes/admin/content/tab/content.html.twig` — 1 label

#### 1.2 Search / Filter Labels (2 files)

3. `src/bundle/Resources/views/themes/admin/ui/search/filters.html.twig` — 6 labels
4. `src/bundle/Resources/views/themes/admin/account/notifications/filters/form_fields.html.twig` — 2 labels

#### 1.3 Content Widget Labels (1 file)

5. `src/bundle/Resources/views/themes/admin/content/widget/content_create.html.twig` — 1 label

#### 1.4 React Labels (2 files) — migrate alongside Twig if applicable

6. `src/bundle/ui-dev/src/modules/universal-discovery/components/content-create-widget/content.create.widget.js` — 1 true `<label>` + 1 `<div>` (skip `<div>`)
7. `src/bundle/ui-dev/src/modules/sub-items/components/sub-items-list/instant.filter.component.js` — 1 `<label>`

#### 1.5 Trash List (1 file)

8. `src/bundle/Resources/views/themes/admin/trash/list.html.twig` — 1 label (with `for`, `form-label` co-class)

---

### Phase 2: Required Labels & Modals (4 files) — MEDIUM PRIORITY ⚠️

These use the `.required` co-class → `required` prop migration.

9. `src/bundle/Resources/views/themes/admin/content/tab/url/modal_add_custom_url.html.twig` — 5 labels (1 required)
10. `src/bundle/Resources/views/themes/admin/url_wildcard/modal_create.html.twig` — 3 labels (2 required)
11. `src/bundle/Resources/views/themes/admin/url_wildcard/update.html.twig` — 3 labels (2 required)
12. `src/bundle/Resources/views/themes/admin/content/modal/user_group_invitation_modal.html.twig` — 1 label

---

### Phase 3: Field Type Edit Labels (5 files) — MEDIUM PRIORITY ⚠️

These are inside field type templates — test carefully.

13. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_author.html.twig` — 2 labels
14. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_binaryfile.html.twig` — 2 labels
15. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_image.html.twig` — 3 labels
16. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_media.html.twig` — 3 labels
17. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_image_asset.html.twig` — 1 label

---

### Phase 4: Complex/Edge Cases (3 files) — LOWER PRIORITY

18. `src/bundle/Resources/views/themes/admin/ui/component/adaptive_filters/adaptive_filters.html.twig` — 1 empty label (keep as legacy + comment)
19. `src/bundle/Resources/views/themes/admin/ui/component/double_input_range/double_input_range.html.twig` — class name swap only (cannot use component)
20. `src/bundle/Resources/views/themes/admin/ui/edit_header.html.twig` — non-`<label>` visual label (migrate to component unless blocked)

---

### Phase 5: Symfony Form Themes (outcome of Phase 0) — LAST

21. `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig` (form_label block only)
22. `src/bundle/Resources/views/themes/admin/content/form_fields.html.twig`
23. `src/bundle/Resources/views/themes/admin/account/change_password/form_fields.html.twig`

---

## JavaScript Selector Updates

### When to Update

Update JavaScript selectors **in the same commit** as the corresponding template migration. Never leave a template migrated to `ids-label` with JS still querying `.ibexa-label`.

### Files Requiring JS Updates

#### `src/bundle/Resources/public/js/scripts/helpers/form.validation.helper.js`

Update these selectors when migrating the first form field template that uses this helper:

```js
❌ BEFORE (lines 21 and 38):
const label = field.querySelector('.ibexa-label');

✅ AFTER:
const label = field.querySelector('.ids-label');
```

Also update the `is-invalid` toggling to use the design system error class:

```js
❌ BEFORE (line 47):
label.classList.toggle('is-invalid', !isValid);

✅ AFTER:
label.classList.toggle('ids-label--error', !isValid);
```

#### `src/bundle/Resources/public/js/scripts/admin.contenttype.edit.js`

```js
❌ BEFORE (line 374):
const labelNode = field?.querySelector('.ibexa-label');

✅ AFTER:
const labelNode = field?.querySelector('.ids-label');
```

#### `src/bundle/Resources/public/js/scripts/sidebar/btn/location.edit.js`

The `--active` modifier is excluded from migration. The base class selector still needs updating:

```js
❌ BEFORE (lines 57–58):
const activeLanguageItem = event.target.closest('.ibexa-instant-filter__group-item')?.querySelector('.ibexa-label');
const allLanguageItems = form.querySelectorAll('.ibexa-instant-filter__group-item .ibexa-label');

✅ AFTER:
const activeLanguageItem = event.target.closest('.ibexa-instant-filter__group-item')?.querySelector('.ids-label');
const allLanguageItems = form.querySelectorAll('.ibexa-instant-filter__group-item .ids-label');
```

The `--active` class manipulation lines (62 and 86) are **not changed** — add TODO comment:

```js
// TODO: Migrate ibexa-label--active to design system equivalent when available
item.classList.remove('ibexa-label--active');
// ...
activeLanguageItem?.classList.add('ibexa-label--active');
```

---

## Per-File Migration Checklist

### Before Migration

- [ ] Read entire file to understand context
- [ ] Count all `ibexa-label` instances
- [ ] Note which have `required` co-class
- [ ] Note which have `form-label` or other context co-classes
- [ ] Note `for` attributes
- [ ] Identify if any JS files target `.ibexa-label` in this context
- [ ] Check for non-`<label>` elements using `ibexa-label` class
- [ ] Check for `ibexa-label--checkbox-radio` (SKIP)
- [ ] Check for `ibexa-label--active` (TODO comment)

### During Migration

- [ ] Replace `<label class="ibexa-label...">` with `<twig:ibexa:label ...>`
- [ ] Move `for` attribute directly to component: `for="field-id"`
- [ ] Convert `.required` co-class to `:required="true"` prop
- [ ] Move context classes to `class` prop
- [ ] Add TODO comments for `--active` and `--small` modifiers
- [ ] Update JS selectors in the same commit (see JS Selector Updates section)
- [ ] Follow 4-space indentation in Twig

### After Migration

- [ ] Twig lint: `php bin/console lint:twig src/bundle/Resources/views/[file-path]`
- [ ] Manually test in browser
- [ ] Verify visual appearance matches original
- [ ] Test label states (required asterisk visible, error color on validation failure)
- [ ] Test JS interactions (form validation, active language toggle)
- [ ] Run PHPUnit tests: `composer test`
- [ ] Create commit with clear message

### Commit Message Format

```
fix: Migrate [short-filename] labels to design-system-twig components

- Replace [N] legacy <label class="ibexa-label"> with <twig:ibexa:label>
- Convert [N] required co-class to required prop
- Update .ibexa-label selector(s) in [js-file] to .ids-label
[- Add TODO comment for ibexa-label--active — no DS equivalent yet]

Refs: LABEL_MIGRATION_PLAN.md
```

---

## Special Cases & Warnings

### 1. Symfony Form Theme Files ⚠️ Phase 0 Required

These files use `label_attr|merge` to inject `ibexa-label` into Symfony-rendered `<label>` elements. `<twig:ibexa:label>` cannot be used directly. **Two options:**

**Option A — Class name swap (simpler):**
```twig
❌ BEFORE:
{%- set label_attr = label_attr|merge({class: (label_attr.class|default('') ~ ' ibexa-label')|trim}) -%}

✅ AFTER:
{%- set label_attr = label_attr|merge({class: (label_attr.class|default('') ~ ' ids-label')|trim}) -%}
```

**Option B — Custom Symfony block override (investigate):**
Research whether the `form_label` block can be overridden to render `<twig:ibexa:label>` by wrapping the block content.

### 2. `double_input_range.html.twig` ⚠️ Class-Only Swap

Cannot use `<twig:ibexa:label>` because label attributes are built as a data structure. Perform a class name swap only.

### 3. Behat Test Files ⚠️ Update CSS Selectors

Once labels are migrated to `ids-label`, these Behat PHP files need selector updates (in the same commit as their corresponding template):

- `src/lib/Behat/Component/CreateNewPopup.php` (lines 83, 86)
- `src/lib/Behat/Page/ObjectStateGroupPage.php` (line 134)
- `src/lib/Behat/Page/ObjectStatePage.php` (line 92)
- `src/lib/Behat/Page/RoleUpdatePage.php` (line 88)
- `src/lib/Behat/Page/ContentUpdateItemPage.php` (lines 111–112)

### 4. SCSS Context Rules — Update Selectors

After migrating, SCSS rules targeting `.ibexa-label` in context selectors must be updated to `.ids-label`. The `ids-label` base styles come from the design system, but context-specific overrides in admin-ui's own SCSS files need updating:

Files with context overrides (update as templates are migrated):
- `_modals.scss`, `_login.scss`, `_search-links-form.scss`, `_filters.scss`
- `_list-filters.scss`, `_adaptive-filters.scss`, `_field-group.scss`
- `_custom-url-form.scss`, `_add-translation.scss`, `_details.scss`, `_tabs.scss`
- `_extra-actions.scss`, `_content-type-edit.scss`
- Field type SCSS: `_base-field.scss`, `_ibexa_image.scss`, `_ibexa_media.scss`, etc.

---

## Style Guide Compliance

### ✅ DO (Twig):

- Pass `for` attribute directly on the component (not `:for`)
- Use `:required="true"` not `required="true"` for boolean props
- Use 4-space indentation inside component blocks
- Pass context classes via `class` prop

### ❌ DON'T (Twig):

- Use `<twig:ibexa:label>` on `<div>` elements (always renders `<label>`)
- Skip the `for` attribute on labels that had one
- Use `.required` co-class alongside the component (use `:required="true"` prop instead)

### ✅ DO (React):

- Import from `@ids-components/components/Label` (same pattern as Button)
- Use `htmlFor` prop for the `for` attribute
- Pass `className` for context-specific classes

### ❌ DON'T (React):

- Migrate `<div className="ibexa-label ibexa-label--checkbox-radio">` (skip)
- Remove `ibexa-label--active` class management from JS without a replacement

---

## Validation Commands

```bash
# Lint Twig syntax (catches syntax errors only)
php bin/console lint:twig src/bundle/Resources/views/themes/admin/[path-to-file]

# Run all tests
composer test

# Code style check
composer check-cs

# Static analysis
composer phpstan

# Frontend checks (TypeScript, ESLint, Prettier)
yarn test
```

---

## Testing Strategy

### Required Testing Approach

**1. Twig Syntax Validation** ✅
```bash
php bin/console lint:twig vendor/ibexa/admin-ui/src/bundle/Resources/views/themes/admin/[file].html.twig
```

**2. Manual Browser Testing** ✅ REQUIRED

For each migrated file:
```bash
rm -rf var/cache/dev && php bin/console cache:clear --no-warmup
```
Then navigate to the page and verify:
- Labels render with correct visual style
- Required labels show asterisk `*`
- Error state labels turn red on form validation failure
- Language selector active state still highlights correctly
- `for` attribute links label to its input correctly

**3. Check Application Logs**
```bash
tail -50 var/log/dev.log | grep -i "exception\|error"
```

### Testing Checklist Per File

- [ ] Twig syntax validates
- [ ] Cache cleared
- [ ] Page loads without exception
- [ ] Labels render with correct font/color (`ids-label` styling)
- [ ] Required asterisk visible for required labels
- [ ] Error state visible when form validation fails
- [ ] `for` attribute still links label to input
- [ ] Context classes (modal__label, etc.) still apply

### Common Issues to Watch For

**1. Missing `ids-label` styles**
```
Problem: Label renders but has no styling (no font/color/spacing)
Cause: ids-label CSS not loaded
Fix: Ensure ids-assets CSS is included in the page's Encore entry
```

**2. Required asterisk missing**
```
Problem: required prop set but asterisk not showing
Cause: ids-label--required CSS rule missing or not loaded
Fix: Check that ids-label--required rule exists in loaded design system stylesheet
```

**3. JS selector breaking after migration**
```
Problem: Form validation no longer highlights labels, or language selector active state broken
Cause: JS still queries .ibexa-label but HTML now has .ids-label
Fix: Update JS selector in same commit as template migration
```

**4. `for` attribute dropped**
```
Problem: Clicking label doesn't focus the associated input
Cause: for attribute missed during migration
Fix: Add for="field-id" prop to <twig:ibexa:label>
```

**5. Context SCSS rules no longer apply**
```
Problem: Labels inside modals/filters have wrong spacing or color
Cause: SCSS context rules still target .ibexa-label
Fix: Update SCSS context rules to .ids-label in the same commit
```

---

## Progress Tracking

**Total Files**: ~23 (17 direct Twig + 3 form theme + 2 React JS + 1 JS-only class swap)  
**Completed**: `admin-ui` phases 1-5 and 8-9 are implemented on `IBX-11236-use-ds-label-component`; related package migrations are also in progress across `vendor/ibexa/*`  
**Remaining**: final review, validation, and any follow-up exception cleanup

### Phase Completion

- [x] Phase 0: Symfony Form Theme Investigation (resolved via class swap in form themes)
- [x] Phase 1: Simple Labels
- [x] Phase 2: Required/Modal Labels
- [x] Phase 3: Field Type Edit Labels
- [x] Phase 4: Complex/Edge Cases (with explicit exceptions tracked)
- [x] Phase 5: Symfony Form Themes (class swap approach)

### Cross-Repository Rollout Status

Repositories currently carrying `IBX-11236` label migration commits on `IBX-11236-use-ds-label-component`:

- `admin-ui`
- `activity-log`
- `calendar`
- `connect`
- `connector-ai`
- `content-tree`
- `corporate-account`
- `discounts`
- `fieldtype-matrix`
- `fieldtype-page`
- `fieldtype-richtext`
- `form-builder`
- `image-picker`
- `measurement`
- `order-management`
- `page-builder`
- `personalization`
- `product-catalog`
- `scheduler`
- `segmentation`
- `seo`
- `share`
- `shipping`
- `site-factory`
- `taxonomy`

---

## Quick Reference

### Twig Component Signature

```twig
<twig:ibexa:label
    for="optional-input-id"
    class="optional-extra-classes"
    :required="true|false"
    :error="true|false"
>
    Label text
</twig:ibexa:label>
```

### React Component Signature

```jsx
import { Label } from '@ids-components/components/Label';

<Label
    htmlFor="optional-input-id"
    className="optional-extra-classes"
    required={true|false}
    error={true|false}
    title="optional-title"
>
    Label text
</Label>
```

### State Quick Map

```
<label class="ibexa-label">          → <twig:ibexa:label>
<label class="ibexa-label required"> → <twig:ibexa:label :required="true">
.is-invalid on label (via JS)        → ids-label--error (update JS toggle target)
ibexa-label--checkbox-radio          → SKIP (checkbox/radio migration)
ibexa-label--active                  → TODO comment (no DS equivalent)
ibexa-label--small on non-<label>    → migrate to <twig:ibexa:label> and preserve class if needed
```

---

**Plan Status**: In Progress  
**Plan Version**: 1.1  
**Date**: March 6, 2026  
**Next Step**: validate package branches, review remaining exceptions, and run package-level verification
