# 📋 BUTTON MIGRATION PLAN: Legacy → design-system-twig Components

**Package**: `ibexa/admin-ui`  
**Date Created**: February 16, 2026  
**Status**: Ready for Review  

---

## ⚠️ CRITICAL ISSUES - READ FIRST

### Issue 1: No `attr` Prop - Pass Attributes Directly

**DO NOT use `attr` prop** - it doesn't exist in Symfony UX Twig Components!

#### ❌ WRONG (Runtime Error)
```twig
<twig:ibexa:button attr="{{ { 'data-foo': 'bar', class: 'my-class' } }}">
```

#### ✅ CORRECT
```twig
<!-- Pass attributes directly -->
<twig:ibexa:button data-foo="bar" class="my-class">

<!-- Dynamic values use : prefix (no {{ }} wrapper) -->
<twig:ibexa:button :data-id="item.id" :disabled="isDisabled">
```

**Why?** Symfony UX Twig Component system automatically collects unrecognized attributes into the `attributes` object. Pass them directly as component props.

---

### Issue 2: No "ghost" Type - Use "tertiary" Instead

**DO NOT use `type="ghost"`** - it's not supported in design-system-twig!

#### ❌ WRONG (Runtime Error)
```twig
<twig:ibexa:button type="ghost">
<twig:ibexa:link variant="button" type="ghost">
```

**Error:** `The option "type" with value "ghost" is invalid. Accepted values are: "primary", "secondary", "tertiary", "secondary-alt", "tertiary-alt".`

#### ✅ CORRECT
```twig
<twig:ibexa:button type="tertiary">
<twig:ibexa:link variant="button" type="tertiary">
```

**Mapping:** Legacy `ibexa-btn--ghost` → `type="tertiary"` in design-system-twig

---

### Issue 3: Icon Names Have Changed

**CRITICAL:** Most legacy icon names have changed in design-system-twig!

#### ❌ WRONG (Icon Won't Display)
```twig
<twig:ibexa:button icon="create">
<twig:ibexa:button icon="back">
<twig:ibexa:button icon="checkmark">
```

#### ✅ CORRECT
```twig
<twig:ibexa:button icon="add">
<twig:ibexa:button icon="arrow-left">
<twig:ibexa:button icon="form-check">
```

**Impact:** 75% of legacy icon names have changed. Using wrong names causes icons to not display.

#### Top 15 Critical Icon Name Changes (by usage frequency)

| Legacy Name | New Name | Files Affected | Priority |
|-------------|----------|----------------|----------|
| `create` | `add` | 15 files | 🔴 CRITICAL |
| `system-information` | `info-circle` | 7 files | 🔴 HIGH |
| `open-newtab` | `open-new-window` | 6 files | 🔴 HIGH |
| `notice` | `alert-error` | 6 files | 🔴 HIGH |
| `options` | `more` | 5 files | 🔴 HIGH |
| `back` | `arrow-left` | 5 files | ⚠️ MEDIUM |
| `caret-down` | `arrow-caret-down` | 4 files | ⚠️ MEDIUM |
| `warning-triangle` | `alert-warning` | 3 files | ⚠️ MEDIUM |
| `warning` | `alert-warning` | 3 files | ⚠️ MEDIUM |
| `checkmark` | `form-check` | 3 files | ⚠️ MEDIUM |
| `date` | `calendar` | 2 files | ⚠️ LOW |
| `mail-open` | `message-email-read` | 2 files | ⚠️ LOW |
| `view` | `visibility` | 1 file | ⚠️ LOW |
| `assign-section` | `assign` | 1 file | ⚠️ LOW |
| `author` | `user-editor` | 1 file | ⚠️ LOW |

#### Quick Reference - Common Icon Mappings

**Direct Matches (No Change):**
```
trash, edit, discard, search, copy, assign-user, download, 
file, lock, bell, filters, drag, arrow-caret-right
```

**Must Change:**
```
create → add
back → arrow-left
checkmark → form-check
bookmark → bookmark-outline
bookmark-active → bookmark-filled
system-information → info-circle
notice → alert-error
options → more
view → visibility
warning → alert-warning
warning-triangle → alert-warning
hide → visibility-hidden
menu → menu-hamburger
date → calendar
mail → message-email
mail-open → message-email-read
assign-section → assign
author → user-editor
restore → archived-restore
focus → focus-centered or focus-target
```

#### How to Verify Icon Names

Search the design system sprite file:
```bash
grep 'id="your-icon-name"' public/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg
```

---

## Table of Contents

1. [Overview](#overview)
2. [Migration Statistics](#migration-statistics)
3. [Icon Name Mapping](#icon-name-mapping)
4. [Migration Patterns](#migration-patterns)
5. [Type Mapping Reference](#type-mapping-reference)
6. [Migration Order by Priority](#migration-order-by-priority)
7. [Per-File Migration Checklist](#per-file-migration-checklist)
8. [Special Cases & Warnings](#special-cases--warnings)
9. [Style Guide Compliance](#style-guide-compliance)
10. [Example Commit History](#example-commit-history)
11. [Validation Commands](#validation-commands)
12. [Testing Strategy](#testing-strategy)
13. [Progress Tracking](#progress-tracking)

---

## Overview

### Goals

1. **Replace legacy button patterns** with `<twig:ibexa:button>` and `<twig:ibexa:link>`
2. **Maintain 100% visual and functional parity**
3. **Document unmapped patterns** with comments for future work
4. **Create clean commit history** for easy review/rollback
5. **Follow Ibexa Twig style guide** throughout

### Approach

- **One file at a time** with individual commits
- **Manual functional testing** per file
- **Git workflow**: LOCAL CHANGES ONLY (no automatic pushes)
- **Comment unmapped variants** rather than forcing incorrect mappings

---

## Migration Statistics

### Files Overview

- **Total Files with Buttons**: 100
- **Total Button Instances**: ~200
  - 134 `<button>` elements (true buttons)
  - 42 `<a>` links styled as buttons
  - 24 `<twig:ibexa:button>` components (already migrated)

### Button Types Distribution

- **Primary**: 35 files
- **Secondary**: 31 files
- **Tertiary**: 16 files
- **Ghost**: 58 files (most common)
- **Info**: 5 files

### Icon Usage

- **Files with Icons**: 72 files (72%)
- **Unique Icon Types**: 57
- **Most Common Icons**: trash (24 files), create (16), edit (14), discard (12)

### Complexity Breakdown

- **Simple Files**: 26 (26%)
- **Form-Related**: 8 (8%)
- **Dynamic/Interactive**: 25 (25%)
- **Modal Dialogs**: 40 (40%)
- **Split Button**: 1 (1%)

---

## Icon Name Mapping

### Complete Icon Inventory

All icons use the same name in design-system-twig - just remove `ibexa_icon_path()` wrapper:

```twig
❌ Legacy: ibexa_icon_path('edit')
✅ New: icon="edit"
```

### Available Icons (Design System - Commonly Used)

**⚠️ IMPORTANT:** This list shows NEW design-system-twig icon names. Many differ from legacy!

**Direct matches from legacy (use as-is):**
- trash, edit, discard, search, copy, assign-user, download, file, lock, bell, filters, drag

**Changed names (see "Icon Name Changes" section above):**
- add (was: create), arrow-left (was: back), form-check (was: checkmark)
- bookmark-outline (was: bookmark), bookmark-filled (was: bookmark-active)
- info-circle (was: system-information), alert-error (was: notice)
- more (was: options), visibility (was: view), alert-warning (was: warning)
- assign (was: assign-section), user-editor (was: author)
- arrow-caret-down (was: caret-down), calendar (was: date)
- message-email (was: mail), message-email-read (was: mail-open)
- visibility-hidden (was: hide), menu-hamburger (was: menu)

**Complete icon list:** 588+ icons available in `/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg`

### Autosave Icon Set

- autosave-error, autosave-off, autosave-on, autosave-saved, autosave-saving

### Dynamic Icons (from variables)

```twig
❌ Legacy: ibexa_icon_path(action.extras.icon)
✅ New: icon="{{ action.extras.icon }}"
```

---

## Migration Patterns

### Pattern 1: Simple Button (Primary/Secondary/Tertiary)

```twig
❌ BEFORE:
<button type="submit" class="btn ibexa-btn ibexa-btn--primary">
    <svg class="ibexa-icon ibexa-icon--small-medium">
        <use xlink:href="{{ ibexa_icon_path('checkmark') }}"></use>
    </svg>
    <span class="ibexa-btn__label">{{ 'button.save'|trans|desc('Save') }}</span>
</button>

✅ AFTER:
<twig:ibexa:button
    type="primary"
    html_type="submit"
    icon="form-check"
    icon_size="small-medium"
>
    {{ 'button.save'|trans|desc('Save') }}
</twig:ibexa:button>
```

**Note:** Icon name changed from legacy `checkmark` to `form-check` in design-system-twig.

### Pattern 2: Icon-Only Button (Ghost/No-Text)

```twig
❌ BEFORE:
<button type="button" class="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text">
    <svg class="ibexa-icon ibexa-icon--tiny-small">
        <use xlink:href="{{ ibexa_icon_path('discard') }}"></use>
    </svg>
</button>

✅ AFTER:
<twig:ibexa:button
    type="tertiary"
    html_type="button"
    icon="discard"
    icon_size="tiny-small"
/>
```

**Note**: `ibexa-btn--ghost` usually maps to `type="tertiary"` in design-system-twig.

### Pattern 3: Link Styled as Button

```twig
❌ BEFORE:
<a href="{{ path('ibexa.user.profile.edit') }}" class="btn ibexa-btn ibexa-btn--ghost ibexa-btn--small">
    <svg class="ibexa-icon ibexa-icon--tiny-small">
        <use xlink:href="{{ ibexa_icon_path('edit') }}"></use>
    </svg>
    <span class="ibexa-btn__label">{{ 'profile.view.edit'|trans|desc('Edit') }}</span>
</a>

✅ AFTER:
<twig:ibexa:link
    href="{{ path('ibexa.user.profile.edit') }}"
    variant="button"
    type="tertiary"
    size="small"
    icon="edit"
    icon_size="tiny-small"
>
    {{ 'profile.view.edit'|trans|desc('Edit') }}
</twig:ibexa:link>
```

### Pattern 4: Button with Data Attributes

```twig
❌ BEFORE:
<button
    type="button"
    class="btn ibexa-btn ibexa-btn--primary ibexa-btn--content-edit"
    data-content-id="{{ contentId }}"
    data-language-code="{{ languageCode }}"
>
    {{ 'button.edit'|trans|desc('Edit') }}
</button>

✅ AFTER:
<twig:ibexa:button
    type="primary"
    html_type="button"
    class="ibexa-btn--content-edit"
    :attributes="{
        'data-content-id': contentId,
        'data-language-code': languageCode,
    }"
>
    {{ 'button.edit'|trans|desc('Edit') }}
</twig:ibexa:button>
```

### Pattern 5: Disabled Button

```twig
❌ BEFORE:
<button type="submit" class="btn ibexa-btn ibexa-btn--primary" disabled>
    {{ 'button.save'|trans|desc('Save') }}
</button>

✅ AFTER:
<twig:ibexa:button
    type="primary"
    html_type="submit"
    :disabled="true"
>
    {{ 'button.save'|trans|desc('Save') }}
</twig:ibexa:button>
```

### Pattern 6: Button with Additional Classes

```twig
❌ BEFORE:
<button type="button" class="btn ibexa-btn ibexa-btn--secondary custom-class another-class">
    {{ 'button.action'|trans|desc('Action') }}
</button>

✅ AFTER:
<twig:ibexa:button
    type="secondary"
    html_type="button"
    class="custom-class another-class"
>
    {{ 'button.action'|trans|desc('Action') }}
</twig:ibexa:button>
```

### Pattern 7: Button with Title/Aria Attributes

```twig
❌ BEFORE:
<button
    type="button"
    class="btn ibexa-btn ibexa-btn--primary"
    title="{{ 'button.add.title'|trans|desc('Add new item') }}"
    aria-label="{{ 'button.add.aria'|trans|desc('Add') }}"
>
    <svg class="ibexa-icon ibexa-icon--small">
        <use xlink:href="{{ ibexa_icon_path('create') }}"></use>
    </svg>
</button>

✅ AFTER:
<twig:ibexa:button
    type="primary"
    html_type="button"
    icon="create"
    icon_size="small"
    :attributes="{
        title: 'button.add.title'|trans|desc('Add new item'),
        'aria-label': 'button.add.aria'|trans|desc('Add'),
    }"
/>
```

### Pattern 8: Conditional Disabled State

```twig
❌ BEFORE:
<button type="submit" class="btn ibexa-btn ibexa-btn--primary" {{ not can_save ? 'disabled' : '' }}>
    {{ 'button.save'|trans|desc('Save') }}
</button>

✅ AFTER:
<twig:ibexa:button
    type="primary"
    html_type="submit"
    :disabled="{{ not can_save }}"
>
    {{ 'button.save'|trans|desc('Save') }}
</twig:ibexa:button>
```

### Pattern 9: Unmapped Variant (KEEP AS LEGACY + COMMENT)

```twig
{# TODO: Migrate to design-system-twig when 'dark-selector' variant is available #}
{# Legacy button pattern - design-system-twig does not support ibexa-btn--dark-selector variant #}
<button type="button" class="btn ibexa-btn ibexa-btn--dark-selector ibexa-btn--selected">
    <svg class="ibexa-icon ibexa-icon--small">
        <use xlink:href="{{ ibexa_icon_path('view-desktop') }}"></use>
    </svg>
</button>
```

---

## Type Mapping Reference

### Button Type Mapping

**⚠️ CRITICAL:** design-system-twig **ONLY** accepts these values:
- `primary`, `secondary`, `tertiary`, `secondary-alt`, `tertiary-alt`

Any other value will cause runtime error!

| Legacy Class | design-system-twig | Notes |
|---|---|---|
| `ibexa-btn--primary` | `type="primary"` | ✅ Direct mapping |
| `ibexa-btn--secondary` | `type="secondary"` | ✅ Direct mapping |
| `ibexa-btn--tertiary` | `type="tertiary"` | ✅ Direct mapping |
| `ibexa-btn--ghost` | **`type="tertiary"`** | ✅ **VERIFIED: Use tertiary, NOT "ghost"!** |
| `ibexa-btn--info` | `type="primary"` | ⚠️ May need new variant |
| `ibexa-btn--dark` | **UNMAPPED** | ❌ Keep legacy + comment |
| `ibexa-btn--dark-selector` | **UNMAPPED** | ❌ Keep legacy + comment |
| `ibexa-btn--secondary-light` | **UNMAPPED** | ❌ Keep legacy + comment |
| `ibexa-btn--selected` | `:attributes` | ⚠️ State class, pass via attributes |
| `ibexa-btn--prevented` | `:disabled="true"` | ⚠️ Use disabled prop |

### Size Mapping

| Legacy Class | design-system-twig | Notes |
|---|---|---|
| `ibexa-btn--small` | `size="small"` | ✅ Direct mapping |
| (no class) | `size="medium"` (default) | ✅ Default size |

### Icon Size Mapping

| Legacy Class | design-system-twig | Typical Button Size |
|---|---|---|
| `ibexa-icon--tiny-small` | `icon_size="tiny-small"` | Usually with `size="small"` |
| `ibexa-icon--small` | `icon_size="small"` | Default button size |
| `ibexa-icon--small-medium` | `icon_size="small-medium"` | Default button size |
| `ibexa-icon--medium` | `icon_size="medium"` | Default button size |

---

## Migration Order by Priority

### Phase 1: Simple Files (26 files) - HIGH PRIORITY ⭐

**Start here** - Low risk, straightforward conversions

#### 1.1 Simple Link-as-Button (11 files)

1. `src/bundle/Resources/views/themes/admin/account/error/credentials_expired.html.twig`
2. `src/bundle/Resources/views/themes/admin/account/profile/view.html.twig`
3. `src/bundle/Resources/views/themes/admin/account/forgot_password/confirmation_page.html.twig`
4. `src/bundle/Resources/views/themes/admin/content/modal/version_conflict.html.twig`
5. `src/bundle/Resources/views/themes/admin/ui/component/table/table_header.html.twig`
6. `src/bundle/Resources/views/themes/admin/ui/field_type/preview/content_fields.html.twig`
7. `src/bundle/Resources/views/themes/admin/user/role/view.html.twig`
8. `src/bundle/Resources/views/themes/admin/section/list.html.twig`
9. `src/bundle/Resources/views/themes/admin/language/list.html.twig`
10. `src/bundle/Resources/views/themes/admin/policy/list.html.twig`
11. `src/bundle/Resources/views/themes/admin/object_state/group/list.html.twig`

#### 1.2 Simple Button-Only (15 files)

12. `src/bundle/Resources/views/themes/admin/ui/component/details/details.html.twig`
13. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/binary_base.html.twig`
14. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_author.html.twig`
15. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_image.html.twig`
16. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/ibexa_media.html.twig`
17. `src/bundle/Resources/views/themes/admin/ui/field_type/edit/relation_base.html.twig`
18. `src/bundle/Resources/views/themes/admin/content/tab/versions/tab.html.twig`
19. `src/bundle/Resources/views/themes/admin/content/tab/translations/tab.html.twig`
20. `src/bundle/Resources/views/themes/admin/content/tab/locations/tab.html.twig`
21. `src/bundle/Resources/views/themes/admin/ui/component/input_text.html.twig`
22. `src/bundle/Resources/views/themes/admin/ui/component/summary_tile/summary_tile.html.twig`
23. `src/bundle/Resources/views/themes/admin/account/bookmarks/toggle_switch.html.twig`
24. `src/bundle/Resources/views/themes/admin/content/modal/add_translation.html.twig`
25. `src/bundle/Resources/views/themes/admin/content/modal/draft_conflict.html.twig`
26. `src/bundle/Resources/views/themes/admin/content/modal/hide_confirmation.html.twig`

### Phase 2: Form-Related (8 files) - MEDIUM PRIORITY ⚠️

**Include form theme files** - Requires careful testing

27. `src/bundle/Resources/views/themes/admin/ui/form_fields.html.twig` ⚠️ **CRITICAL: Form theme file**
28. `src/bundle/Resources/views/themes/admin/account/forgot_password/index.html.twig`
29. `src/bundle/Resources/views/themes/admin/ui/global_search.html.twig`
30. `src/bundle/Resources/views/themes/admin/account/bookmarks/list.html.twig`
31. `src/bundle/Resources/views/themes/admin/user/invitation/modal.html.twig`
32. `src/bundle/Resources/views/themes/admin/content/widget/content_create.html.twig` (partially migrated)
33. `src/bundle/Resources/views/themes/admin/content/widget/content_edit.html.twig` (partially migrated)
34. `src/bundle/Resources/views/themes/admin/ui/search/filters.html.twig` (partially migrated)

### Phase 3: Modal Dialogs (40 files) - MEDIUM-HIGH PRIORITY

**Confirmation Modals** - Start with these (consistent pattern)

35-49. Various confirmation modal files (`delete_confirmation.html.twig`, etc.)

**Content Management Modals** - Then these (more complex)

50-74. Various content/language/section/user modals

### Phase 4: Complex Dynamic (24 files) - LOWER PRIORITY

**High JS interaction, complex state management**

75-98. Dynamic interaction files

### Phase 5: Split Button (1 file) - KEEP AS-IS 📌

99. `src/bundle/Resources/views/themes/admin/ui/component/split_btn/split_btn.html.twig` ⚠️ **Keep legacy per requirement**

---

## Per-File Migration Checklist

### Before Migration

- [ ] Read entire file to understand context
- [ ] Identify all button/link instances (count them)
- [ ] Note any special classes or data attributes
- [ ] Check for dynamic icon names (variables)
- [ ] Identify button type (primary/secondary/tertiary/ghost)
- [ ] Note size modifiers (small/default)
- [ ] Check for unmapped variants
- [ ] Review surrounding code for dependencies

### During Migration

- [ ] Replace `<button>` with `<twig:ibexa:button>`
- [ ] Replace button-styled `<a>` with `<twig:ibexa:link variant="button">`
- [ ] Map type correctly (primary/secondary/tertiary)
- [ ] Map size correctly (small/medium)
- [ ] Convert icon: `ibexa_icon_path('name')` → `icon="name"`
- [ ] Map icon size to button size appropriately
- [ ] Preserve data attributes via `:attributes`
- [ ] Preserve custom classes via `class` prop
- [ ] Preserve translations with `|trans|desc()`
- [ ] Add TODO comments for unmapped variants
- [ ] Follow snake_case naming (variables, attributes)
- [ ] Use property shorthand where applicable
- [ ] Add trailing commas in attribute objects
- [ ] Maintain proper 4-space indentation
- [ ] Remove trailing whitespace

### After Migration

- [ ] Check Twig syntax: `php bin/console lint:twig src/bundle/Resources/views/[file-path]`
- [ ] Manually test in browser (if possible)
- [ ] Verify visual appearance matches original
- [ ] Test button interactions (click, hover, disabled states)
- [ ] Run PHPUnit tests: `composer test`
- [ ] Run code style check: `composer check-cs` (if applicable)
- [ ] Create commit with clear message

### Commit Message Format

```
fix: Migrate [short-filename] to design-system-twig components

- Replace [N] legacy <button> elements with <twig:ibexa:button>
- Replace [N] button-styled links with <twig:ibexa:link variant="button">
- Map button types: [primary/secondary/tertiary used in file]
- Preserve all data attributes and functionality
[- Add TODO comment for [unmapped-variant] - needs design system support]

Refs: BUTTON_MIGRATION_PLAN.md
```

---

## Special Cases & Warnings

### 1. Form Theme File (`ui/form_fields.html.twig`) ⚠️ CRITICAL

This file defines Symfony form widget blocks. Migration requires **careful testing**.

**Approach**: Test each form widget block individually. May need to keep some legacy patterns for Symfony compatibility.

### 2. Split Button Component ⚠️ KEEP AS-IS

Keep as-is per requirement. Add explanatory comment:

```twig
{# Legacy split button component - kept as-is #}
{# design-system-twig does not yet have a split button component #}
{# TODO: Migrate when design-system-twig supports split buttons #}
```

### 3. Content Preview (`content/content_preview.html.twig`) ⚠️ UNMAPPED VARIANTS

Has unmapped variants: `ibexa-btn--dark-selector`, `ibexa-btn--secondary-light`, `ibexa-btn--selected`

**Approach**: Keep legacy + add TODO comments for each unmapped variant.

---

## Style Guide Compliance

Follow **Ibexa Twig Style Guide**:

### ✅ DO:

- Use `snake_case` for all variables and attributes
- Add trailing commas in multi-line objects
- Use property shorthand: `user_id,` instead of `user_id: user_id,`
- Keep `|trans|desc()` together in translations
- Escape data attributes: `|e('html_attr')`
- Use 4-space indentation
- Remove trailing whitespace

### ❌ DON'T:

- Use `camelCase` for variables
- Forget `|desc()` after `|trans`
- Leave data attributes unescaped
- Use 2-space indentation
- Add trailing whitespace

---

## Example Commit History

```bash
git commit -m "fix: Migrate account/profile/view.html.twig to design-system-twig

- Replace 1 edit link with <twig:ibexa:link variant=\"button\">
- Map to tertiary type with small size
- Add edit icon
- Preserve all functionality

Refs: BUTTON_MIGRATION_PLAN.md"
```

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
```

**Note**: `lint:twig` only catches **syntax errors**, not runtime errors like incorrect prop types or non-existent props. Manual browser testing is required.

---

## Testing Strategy

### ⚠️ Important: Behat Tests Are Unreliable

**DO NOT rely on Behat tests** for validating button migrations. The test environment has pre-existing issues that mask template errors.

### Required Testing Approach

**1. Twig Syntax Validation** ✅
```bash
php bin/console lint:twig vendor/ibexa/admin-ui/src/bundle/Resources/views/themes/admin/[file].html.twig
```
- Catches: Syntax errors, unclosed tags, invalid Twig
- Doesn't catch: Wrong prop types, runtime errors, non-existent props

**2. Manual Browser Testing** ✅ **REQUIRED**
```bash
# Clear cache
rm -rf var/cache/dev && php bin/console cache:clear --no-warmup

# Navigate to page in browser and verify:
# - Page loads without exception
# - Buttons render correctly
# - Buttons function correctly (clicks, modals, etc.)
# - All data attributes present in DOM (inspect element)
```

**3. Check Application Logs**
```bash
tail -50 var/log/dev.log | grep -i "exception\|error"
```

### Testing Checklist Per File

For each migrated file:

- [ ] ✅ Twig syntax validates (`php bin/console lint:twig`)
- [ ] ✅ Cache cleared
- [ ] ✅ Page loads without exception (check logs)
- [ ] ✅ Buttons render with correct styling
- [ ] ✅ Buttons are clickable
- [ ] ✅ Modal triggers work (if applicable)
- [ ] ✅ Data attributes present in DOM (inspect)
- [ ] ✅ Icons appear correctly
- [ ] ✅ Disabled state works (if applicable)

### Common Issues to Watch For

**1. `attr` Prop Error** (Runtime)
```
Error: A "attr" prop was passed when creating the component...
the value is not a scalar (it's a "array")
```
**Fix**: Pass attributes directly, not as `attr` prop.

**2. `type="ghost"` Error** (Runtime)
```
Error: The option "type" with value "ghost" is invalid. 
Accepted values are: "primary", "secondary", "tertiary", 
"secondary-alt", "tertiary-alt".
```
**Fix**: Use `type="tertiary"` instead of `type="ghost"`.

**3. Dynamic Attribute Syntax Error**
```twig
❌ WRONG: :data-id="{{ item.id }}"
✅ CORRECT: :data-id="item.id"
```
**Rule**: Attributes with `:` prefix should NOT have `{{ }}` wrapper.

**4. Boolean Attributes**
```twig
❌ WRONG: download="true"
✅ CORRECT: download
```

**5. Wrong Icon Name** (Runtime - Icon Missing/Not Showing)
```
Problem: Icon doesn't appear in button even though icon prop is set.
```

**Cause:** Legacy icon name doesn't exist in new design system.

**Fix:** Check "Icon Name Changes" section and use correct new name.

**Examples:**
```twig
❌ WRONG: icon="create"
✅ CORRECT: icon="add"

❌ WRONG: icon="back"
✅ CORRECT: icon="arrow-left"

❌ WRONG: icon="checkmark"
✅ CORRECT: icon="form-check"

❌ WRONG: icon="system-information"
✅ CORRECT: icon="info-circle"

❌ WRONG: icon="notice"
✅ CORRECT: icon="alert-error"
```

**How to verify icon exists:**
```bash
grep 'id="your-icon-name"' public/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg
```

---

## Progress Tracking

**Total Files**: 100  
**Completed**: 8 files  
**Remaining**: 92 files

### Completed Files (8/100)

✅ **Phase 1 - Simple Files (5/26)**
1. `account/error/credentials_expired.html.twig` - 1 link button (commit: f0a8b93b8)
2. `account/profile/view.html.twig` - 1 edit link (commit: a39a94cdf)
3. `account/forgot_password/confirmation_page.html.twig` - 2 link buttons (commit: 9e2cbd15e)
4. `ui/component/details/details.html.twig` - 1 toggle button (commit: a740b26c1)
5. `ui/field_type/edit/binary_base.html.twig` - 1 upload button (commit: 29c9acb47, c152dfef6)

✅ **Phase 3 - Modal Dialogs (1/40)**
6. `content/modal/version_conflict.html.twig` - 2 buttons (commits: bf1fb5a35, 00e019ed4 fix)

✅ **Phase 1/2 - Mixed (2/26)**
7. `ui/field_type/preview/content_fields.html.twig` - 1 download link (commits: 6aa331c8b, facfbfa0c fix)
8. `section/list.html.twig` - 4 buttons (commits: d07ef6962, 110c3e3b3 fix)

### Phase Completion

- [ ] Phase 1: Simple Files (7/26 completed - 27%)
- [ ] Phase 2: Form-Related (0/8 files)
- [ ] Phase 3: Modal Dialogs (1/40 files - 2.5%)
- [ ] Phase 4: Complex Dynamic (0/24 files)
- [ ] Phase 5: Split Button (0/1 file - document only)

### Known Issues Fixed

- ✅ **`attr` prop error** - Fixed in 3 files (version_conflict, content_fields, section/list)
- ✅ Attribute passing syntax corrected
- ✅ Dynamic attribute binding (`:` prefix) syntax verified
- ✅ **`type="ghost"` error** - Fixed in 2 files (4 instances total)
  - `section/list.html.twig`: 3 buttons changed to `type="tertiary"`
  - `content_fields.html.twig`: 1 button changed to `type="tertiary"`
- ✅ **Icon name mismatches** - Fixed in 2 files (3 icon names corrected)
  - `section/list.html.twig`: `create` → `add`, `assign-section` → `assign`
  - `confirmation_page.html.twig`: `back` → `arrow-left`
  - Documented 15+ critical icon name changes for future migrations

---

## Quick Reference

### Button Type Quick Map

```
ibexa-btn--primary     → type="primary"
ibexa-btn--secondary   → type="secondary"
ibexa-btn--tertiary    → type="tertiary"
ibexa-btn--ghost       → type="tertiary"
```

### Common Icon Names

```
Trash/Delete → icon="trash"
Edit         → icon="edit"
Create/Add   → icon="create"
Close/Cancel → icon="discard"
Back         → icon="back"
```

---

## Summary

This migration plan provides everything needed for systematic button migration:

✅ Complete inventory (100 files)  
✅ Icon mapping (57 icons)  
✅ 9 migration patterns  
✅ Type/size mapping reference  
✅ Prioritized execution order  
✅ Per-file checklist  
✅ Style guide compliance  
✅ Progress tracking  

**Ready for Execution**: Follow file-by-file with individual commits.

---

**Plan Status**: ✅ Ready for Review  
**Plan Version**: 1.0  
**Date**: February 16, 2026  
**Next Step**: Review plan → Execute Phase 1
