# TAG & BADGE MIGRATION PLAN: Legacy ibexa-tag / ibexa-badge → design-system-twig Components

**Package**: `ibexa/admin-ui` (+ cross-package coordination)
**Date Created**: March 9, 2026
**Last Updated**: March 10, 2026
**Status**: Ready for Review

---

## CRITICAL DIFFERENCES — READ FIRST

### The Naming Inversion Trap

The names in admin-ui and design-system are **opposite** to what you might expect:

| Admin-UI Class | Appearance | DS Component | DS Class |
|---|---|---|---|
| `ibexa-tag` (removable) | Pill with ✕ button | `<twig:ibexa:chip>` | `ids-chip` |
| `ibexa-tag` (non-removable) | Plain pill | `<twig:ibexa:tag>` | `ids-tag` |
| `ibexa-badge` (colored pill, no ✕) | Colored text pill | `<twig:ibexa:tag>` | `ids-tag` |

**Key rule:**
- `ibexa-tag` with a remove button → **DS chip** (chip = removable)
- `ibexa-tag` without a remove button → **DS tag** (tag = non-removable)
- `ibexa-badge` → **DS tag** (badge = non-removable colored label)

### DO NOT use `<twig:ibexa:badge>` for `ibexa-badge`

The DS `<twig:ibexa:badge>` is a **notification counter** ("99+"), not a colored text label. Using it for admin-ui's `ibexa-badge` would be semantically and visually wrong.

---

### Issue 1: `tag.html.twig` Cannot Be Fully Migrated Yet

The canonical `@ibexadesign/ui/tag.html.twig` include template has a **spinner/loading state** (`ibexa-tag__spinner`, `is_loading_state`). The DS chip component has no spinner support.

**Decision: Do NOT migrate `tag.html.twig` itself.** Instead:
- Migrate all **inline usages** of `ibexa-tag` that don't go through `tag.html.twig`
- Leave `tag.html.twig` as legacy with a TODO comment

---

### Issue 2: ibexa-badge--status Uses a Ghost Dot Pattern

`ibexa-badge--status` + `ibexa-badge--success` (or `--danger`) renders a **colored dot + text** via CSS `::before`.

The DS tag has ghost types: `success-ghost`, `error-ghost`, `neutral-ghost`. These render an `ids-tag__ghost-dot` element, providing the same dot + text visual.

**Mapping:**
- `ibexa-badge--status ibexa-badge--success` → `<twig:ibexa:tag type="success-ghost">`
- `ibexa-badge--status ibexa-badge--danger` → `<twig:ibexa:tag type="error-ghost">`
- `ibexa-badge--status` without color → `<twig:ibexa:tag type="neutral-ghost">`

---

### Issue 3: `--custom-colors` and `--complementary` Are Unmapped

`ibexa-badge--custom-colors` (used in `payment`, `shipping`, `order-management`) drives colors via CSS variables (`--primary-color`, `--secondary-color`). The DS tag has no arbitrary color override. **Do not migrate these — keep as legacy with TODO comment.**

`ibexa-badge--complementary` (used in `personalization`) has no DS tag equivalent. **Keep as legacy with TODO comment.**

---

### Issue 4: JS Selectors Must Update With Template

Every JS file that targets `.ibexa-tag`, `.ibexa-tag__remove-btn`, `.ibexa-tag__content`, `.ibexa-tag__spinner` must be updated **in the same commit** as the corresponding template migration.

The DS chip uses `.ids-chip__delete` (not `.ibexa-tag__remove-btn`) and `.ids-chip__content`.

---

### Issue 5: `ibexa-taggify` and `ibexa-tag-view-select` Are Out of Scope

These are distinct, complex widget families with no DS equivalents. **Do not migrate them.**

---

### Issue 6: Cross-Package Scope — `page_title.html.twig` is a Breaking Change

`page_title.html.twig` lives in `admin-ui` but is consumed with `tag`/`tag_extra_classes` variables by 6 templates in other packages (`corporate-account`, `product-catalog`, `payment` ×2, `shipping` ×2). Migrating its `{% block tag %}` requires coordinated changes across all those packages in a separate phase.

---

## Table of Contents

1. [Component Signatures](#component-signatures)
2. [Type/Modifier Mapping Reference](#typemodifier-mapping-reference)
3. [Migration Patterns](#migration-patterns)
4. [Complete Occurrence Inventory](#complete-occurrence-inventory)
5. [Migration Order by Priority](#migration-order-by-priority)
6. [JavaScript Selector Updates](#javascript-selector-updates)
7. [Per-File Migration Checklist](#per-file-migration-checklist)
8. [Special Cases & Warnings](#special-cases--warnings)
9. [Style Guide Compliance](#style-guide-compliance)
10. [Validation Commands](#validation-commands)
11. [Testing Strategy](#testing-strategy)
12. [Progress Tracking](#progress-tracking)

---

## Component Signatures

### DS Tag — Non-removable colored label

```twig
<twig:ibexa:tag
    type="primary"
    size="medium"
    icon="optional-icon-name"
    :isDark="false"
>
    Label text
</twig:ibexa:tag>
```

**Props:**
- `type` (required): `primary` | `primary-alt` | `info` | `success` | `warning` | `error` | `neutral` | `icon-tag` | `success-ghost` | `error-ghost` | `neutral-ghost`
- `size`: `medium` (default) | `small`
- `icon`: icon name string (optional; use with `type="icon-tag"`)
- `isDark`: bool (default `false`)

### DS Chip — Removable tag

```twig
<twig:ibexa:chip
    :isDeletable="true"
    :error="false"
    :disabled="false"
>
    Label text
</twig:ibexa:chip>
```

**Props:**
- `isDeletable`: bool (default `true`) — controls whether the ✕ button appears
- `error`: bool (default `false`) — red error state
- `disabled`: bool (default `false`) — greys out the chip and disables interaction
- The delete button renders as: `<button class="ids-chip__delete">` (handled internally)

---

## Type/Modifier Mapping Reference

### `ibexa-tag` Modifier → DS Component

| Legacy Class | DS Component | DS Prop | Notes |
|---|---|---|---|
| `ibexa-tag` + remove button | `<twig:ibexa:chip>` | `isDeletable="true"` (default) | Removable |
| `ibexa-tag` + no remove button | `<twig:ibexa:tag>` | `type="primary"` (or match color) | Non-removable |
| `ibexa-tag--deletable` | `<twig:ibexa:chip>` | `isDeletable="true"` | CSS class controlled visibility |
| `ibexa-tag--primary` | `<twig:ibexa:tag>` | `type="primary"` | Direct match |
| `ibexa-tag--secondary` | `<twig:ibexa:tag>` | `type="neutral"` | Renamed |
| `ibexa-tag--info` | `<twig:ibexa:tag>` | `type="info"` | Direct match |
| `ibexa-tag--danger` | `<twig:ibexa:tag>` | `type="error"` | Renamed |
| `ibexa-tag--success` | `<twig:ibexa:tag>` | `type="success"` | Direct match |
| `ibexa-tag--complementary` | UNMAPPED | — | Keep legacy + TODO comment |

> No color modifiers for `ibexa-tag` are currently used in any template across all packages — only `--deletable` is active.

### `ibexa-badge` Modifier → DS Tag `type`

| Legacy Class | DS `type` | Notes |
|---|---|---|
| `ibexa-badge` (no modifier) | `type="neutral"` | Default |
| `ibexa-badge--primary` | `type="primary"` | Direct match |
| `ibexa-badge--secondary` | `type="neutral"` | Renamed |
| `ibexa-badge--info` | `type="info"` | Direct match |
| `ibexa-badge--warning` | `type="warning"` | Direct match |
| `ibexa-badge--danger` | `type="error"` | Renamed |
| `ibexa-badge--success` | `type="success"` | Direct match |
| `ibexa-badge--small` | `size="small"` | Direct match |
| `ibexa-badge--complementary` | UNMAPPED | Keep legacy + TODO comment |
| `ibexa-badge--status + --success` | `type="success-ghost"` | Dot indicator pattern |
| `ibexa-badge--status + --danger` | `type="error-ghost"` | Dot indicator pattern |
| `ibexa-badge--status` (no color) | `type="neutral-ghost"` | Dot indicator pattern |
| `ibexa-badge--ghost` | `success-ghost`/`error-ghost`/`neutral-ghost` | Use based on context color |
| `ibexa-badge--custom-colors` | UNMAPPED | Keep legacy + TODO comment |

---

## Migration Patterns

### Pattern 1: Non-removable Inline Tag → DS Tag

```twig
❌ BEFORE:
{% for role_assigment in roles %}
    <div class="ibexa-tag">
        <div class="ibexa-tag__content">
            {{ role_assigment.identifier }}
        </div>
    </div>
{% endfor %}

✅ AFTER:
{% for role_assigment in roles %}
    <twig:ibexa:tag type="neutral">
        {{ role_assigment.identifier }}
    </twig:ibexa:tag>
{% endfor %}
```

---

### Pattern 2: Badge with Color Modifier → DS Tag

```twig
❌ BEFORE:
<span class="ibexa-badge ibexa-badge--success">{{ 'label.active'|trans|desc('Active') }}</span>

✅ AFTER:
<twig:ibexa:tag type="success">
    {{ 'label.active'|trans|desc('Active') }}
</twig:ibexa:tag>
```

---

### Pattern 3: Badge with Small Size → DS Tag with size

```twig
❌ BEFORE:
<span class="badge ibexa-badge ibexa-badge--success ibexa-badge--small">
    {{ 'label.active'|trans|desc('Active') }}
</span>

✅ AFTER:
<twig:ibexa:tag type="success" size="small">
    {{ 'label.active'|trans|desc('Active') }}
</twig:ibexa:tag>
```

---

### Pattern 4: Status/Ghost Dot Badge → DS Ghost Tag

```twig
❌ BEFORE:
{% set badge_class = 'ibexa-badge--status ibexa-badge--' ~ (is_active ? 'success' : 'danger') %}
<span class="ibexa-badge {{ badge_class }}">{{ label }}</span>

✅ AFTER:
<twig:ibexa:tag :type="is_active ? 'success-ghost' : 'error-ghost'">
    {{ label }}
</twig:ibexa:tag>
```

---

### Pattern 5: Dynamic Badge Modifier String → DS Tag

Used in `personalization` and `product-catalog` where a `modifier` variable maps to badge class:

```twig
❌ BEFORE:
{% set modifier = states_colors_map[state] %}
<span class="ibexa-badge ibexa-badge--{{ modifier }}">{{ state|trans }}</span>

✅ AFTER:
{# states_colors_map values must be updated to DS type names: danger→error, secondary→neutral #}
{% set tag_type = states_colors_map[state] %}
<twig:ibexa:tag :type="tag_type">{{ state|trans }}</twig:ibexa:tag>
```

> **Note:** Update the `states_colors_map` in the controller/template to use DS type names (`error` not `danger`, `neutral` not `secondary`).

---

### Pattern 6: `ibexa-tag` on `<li>` element → DS Tag inside `<li>`

DS tag always renders a `<div>`. When `ibexa-tag` is applied directly to a `<li>`, the companion class must move to the wrapper:

```twig
❌ BEFORE:
<li class="ibexa-selection__item ibexa-tag">
    <div class="ibexa-tag__content">{{ options[selectedIndex] }}</div>
</li>

✅ AFTER:
<li class="ibexa-selection__item">
    <twig:ibexa:tag type="neutral">
        {{ options[selectedIndex] }}
    </twig:ibexa:tag>
</li>
```

---

### Pattern 7: `page_title.html.twig` Badge Block (Cross-Package) → DS Tag

The block in `admin-ui` must change from `tag_extra_classes` (CSS modifier string) to `tag_type` (DS type name). Every downstream caller must be updated simultaneously:

```twig
❌ BEFORE (admin-ui/page_title.html.twig):
{% block tag %}
    {% if tag is defined %}
        {% set attr = tag_attr|default({})|merge({
            class: (tag_attr.class|default('') ~ ' ibexa-badge ' ~ tag_extra_classes|default(''))|trim,
        }) %}
        <div {{ html.attributes(attr) }}>{{ tag }}</div>
    {% endif %}
{% endblock %}

✅ AFTER (admin-ui/page_title.html.twig):
{% block tag %}
    {% if tag is defined %}
        <twig:ibexa:tag
            :type="tag_type|default('neutral')"
            class="{{ tag_extra_classes|default('') }}"
        >
            {{ tag }}
        </twig:ibexa:tag>
    {% endif %}
{% endblock %}
```

```twig
❌ BEFORE (caller, e.g. product-catalog/product/view.html.twig):
{% include '@ibexadesign/ui/page_title.html.twig' with {
    title: product.getName(),
    tag: product.getCode(),
    tag_extra_classes: 'ibexa-badge--info',
} %}

✅ AFTER (caller):
{% include '@ibexadesign/ui/page_title.html.twig' with {
    title: product.getName(),
    tag: product.getCode(),
    tag_type: 'info',
} %}
```

> **Note on `tag_extra_classes` backward compatibility:** The `class` prop is kept in the `AFTER` version of `page_title.html.twig` so callers that pass arbitrary extra classes for non-type purposes still work. The `tag_extra_classes` variable is preserved as a `class` passthrough — only the type/color selection moves to `tag_type`.

---

### Pattern 8: Removable Tag via `tag.html.twig` → DS Chip (Future — HOLD)

`tag.html.twig` has a spinner/loading state. **Not migrated in this plan.** Add TODO comment only:

```twig
{# TODO: Migrate to <twig:ibexa:chip> when DS chip supports a loading/spinner state #}
{# Legacy removable tag template — kept as-is per TAG_BADGE_MIGRATION_PLAN.md #}
```

---

### Pattern 9: Removable Inline Tag (No Spinner) → DS Chip

For packages that write `ibexa-tag ibexa-tag--deletable` inline (not via `tag.html.twig`) and have no spinner:

```twig
❌ BEFORE (workflow/apply_transition_widget.html.twig):
<div class="ibexa-workflow-apply-transition__selected-user ibexa-tag ibexa-tag--deletable">
    <span class="ibexa-workflow-apply-transition__user-name"></span>
    <button type="button" class="ibexa-tag__remove-btn ibexa-tag__remove-btn--remove-reviewer">
        <svg class="ibexa-icon ibexa-icon--small-medium">
            <use xlink:href="{{ ibexa_icon_path('circle-close') }}"></use>
        </svg>
    </button>
</div>

✅ AFTER:
<twig:ibexa:chip class="ibexa-workflow-apply-transition__selected-user">
    <span class="ibexa-workflow-apply-transition__user-name"></span>
</twig:ibexa:chip>
```

JS update required: `.ibexa-tag__remove-btn--remove-reviewer` → `.ids-chip__delete`

---

### Pattern 10: Unmapped — Keep as Legacy + Comment

```twig
{# TODO: Migrate to design-system-twig when ibexa-badge--custom-colors has a DS equivalent #}
{# Legacy ibexa-badge--custom-colors — requires CSS variable color override, no DS tag equivalent #}
<span
    class="ibexa-badge ibexa-badge--custom-colors"
    style="--primary-color: {{ fg_color }}; --secondary-color: {{ bg_color }};"
>
    {{ label }}
</span>
```

---

## Complete Occurrence Inventory

### `ibexa-tag` — All Files Across All Packages

#### Group A: Simple non-removable inline — migrate to `<twig:ibexa:tag>` (Phase 1 & 2)

| # | Package | File | Line(s) | Notes |
|---|---------|------|---------|-------|
| A1 | admin-ui | `account/profile/view.html.twig` | 96–99 | Role assignment tags in loop; no JS |
| A2 | admin-ui | `limitation/udw_limitation_value.html.twig` | 43–46 | Static "location deleted" in `{% else %}` branch only |
| A3 | admin-ui | `ui/field_type/preview/content_fields.html.twig` | 153–156 | Multi-select preview on `<li>` — wrap in `<li>` |
| A4 | admin-ui | `ui/field_type/preview/content_fields.html.twig` | 161, 165 | Single-select preview: class via `attr|merge` — special case (see Special Cases §5) |
| A5 | admin-ui | `ui/search/criteria_tags.html.twig` | 5–10 | Language filter, no remove button, manually inlined |

#### Group B: Canonical template — HOLD (spinner)

| # | Package | File | Status |
|---|---------|------|--------|
| B1 | admin-ui | `ui/tag.html.twig` | **HOLD** — spinner has no DS equivalent; add TODO comment |

#### Group C: Callers of `tag.html.twig` — all HOLD (depend on B1)

| # | Package | File | Line | `is_deletable` | Spinner? | Data attrs | Blocker |
|---|---------|------|------|----------------|----------|-----------|---------|
| C1 | admin-ui | `content_type/relation_list_form_fields.html.twig` | 32 | true (default) | no | none | `data-template` HTML string injection |
| C2 | admin-ui | `content_type/relation_list_form_fields.html.twig` | 47 | true (default) | no | none | Removable, depends on B1 |
| C3 | admin-ui | `content_type/relation_form_fields.html.twig` | 32 | true (default) | no | none | `data-template` HTML string injection |
| C4 | admin-ui | `content_type/relation_form_fields.html.twig` | 47 | true (default) | no | none | Removable, depends on B1 |
| C5 | admin-ui | `user/role_assignment/create_tag_list_item.html.twig` | 2 | true (default) | no | `data-content-id` | Removable, JS-driven |
| C6 | admin-ui | `limitation/udw_limitation_value_list_item.html.twig` | 2 | true (default) | yes | `data-location-id` | Removable + spinner; depends on B1 |
| C7 | personalization | `scenarios/form_fields/preview.html.twig` | 52, 84, 116 | true (default) | no | `data-input-id` | Removable, JS-driven (scenarios.preview.js) |
| C8 | personalization | `parts/category_path_select_tag.html.twig` | 1–8 | true (default) | no | `data-value` | Removable, JS-driven (category.path.select.js) |
| C9 | segmentation | `segmentation/page_builder/block/config/segmentation_item.html.twig` | — | true (default) | no | none (data set by JS) | Used in `data-template`; JS injects and populates via `targeted.content.map.js` |

#### Group D: Inline removable (no spinner) — migrate to `<twig:ibexa:chip>` (Phase 3)

| # | Package | File | Line(s) | Data attrs | JS file(s) |
|---|---------|------|---------|-----------|-----------|
| D1 | workflow | `ibexa_workflow/apply_transition_widget.html.twig` | 48–54 | none on tag; JS writes to inner span | `workflow.transition.apply.widget.js` (line 14: `.ibexa-tag__remove-btn--remove-reviewer`) |
| D2 | personalization | `personalization/models/form_fields.html.twig` | 31–41 | none on tag; `data-items-type` on button | `model.edit.editorial.js` (lines 37, 68: `.ibexa-tag__remove-btn`) |

#### Group E: JS selector-only files (no own template — update when their template migrates)

| # | Package | File | Selectors | Corresponding template |
|---|---------|------|-----------|------------------------|
| E1 | admin-ui | `js/scripts/admin.limitation.pick.js` | `.ibexa-tag`, `__content`, `__spinner`, `__remove-btn` | B1 (`tag.html.twig`) |
| E2 | admin-ui | `js/scripts/admin.search.filters.js` | `.ibexa-tag`, `__remove-btn--${tagType}` | B1 / `search_tag.html.twig` |
| E3 | admin-ui | `js/scripts/admin.contenttype.relation.default.location.js` | `__remove-btn` | C3/C4 |
| E4 | admin-ui | `js/scripts/udw/select.location.js` | `__content`, `__spinner`, `__remove-btn` | B1 |
| E5 | workflow | `js/workflow.transition.apply.widget.js` | `__remove-btn--remove-reviewer` | D1 |
| E6 | personalization | `js/model.edit.editorial.js` | `__remove-btn` | D2 |
| E7 | personalization | `js/scenarios.preview.js` | `__remove-btn` | C7 |
| E8 | personalization | `js/core/category.path.select.js` | `.ibexa-tag`, `__remove-btn` | C8 |
| E9 | segmentation | `js/widgets/targeted.content.map.js` | `.ibexa-tag`, `__content`, `__remove-btn` | C9 |
| E10 | product-catalog | `js/filterConfig/base.filter.config.js` | `__remove-btn` | product-catalog filter tag template |
| E11 | page-builder | `src/lib/Behat/Component/Blocks/TargetingBlock.php` | `__content` (Behat locator) | C9 (segmentation template) |

#### Group F: React Tag component

| # | Package | File | Usage |
|---|---------|------|-------|
| F1 | admin-ui | `ui-dev/modules/common/tag/tag.js` | Component definition — renders `ibexa-tag`, `ibexa-tag--deletable`, `__content`, `__remove-btn` |
| F2 | admin-ui | `ui-dev/…/search/search.tags.js` | 3 removable tag usages (content-type, section, subtree) |
| F3 | admin-ui | `ui-dev/…/common/taggify/taggify.js` | Used inside taggify (taggify is out of scope but shares this component) |

#### Group G: SCSS context overrides (update when class changes)

| # | Package | File | What |
|---|---------|------|------|
| G1 | admin-ui | `scss/_tag.scss` | Root `.ibexa-tag` block definition |
| G2 | admin-ui | `scss/ui/modules/common/_taggify.scss` | `.ibexa-tag` margin override in taggify context |
| G3 | product-catalog | `scss/_edit-catalog-list-filter-tag.scss` | `.ibexa-tag` override (white-space: nowrap) |
| G4 | product-catalog | `scss/_edit-catalog-filter-preview.scss` | `.ibexa-tag` override (margin + padding) |

---

### `ibexa-badge` — All Files Across All Packages

#### Group H: Standalone badges — migrate to `<twig:ibexa:tag>` when accessible modifier maps

| # | Package | File | Line(s) | Modifiers | Migrate to | Phase |
|---|---------|------|---------|-----------|-----------|-------|
| H1 | admin-ui | `ui/page_title.html.twig` | 12 | dynamic via `tag_extra_classes` | DS tag with `tag_type` var | Phase 5 (cross-package) |
| H2 | connector-ai | `connector_ai/action_configuration/ui/status_badge.html.twig` | 3, 6, 10 | `--status`, `--success`, `--danger` | `success-ghost` / `error-ghost` | Phase 4 |
| H3 | corporate-account | `corporate_account/individual/list.html.twig` | 65, 69 | `--info`, `--secondary`, `--small` | `type="info"` / `type="neutral"` + `size="small"` | Phase 4 |
| H4 | corporate-account | `corporate_account/company/list.html.twig` | 94, 98 | `--success`, `--secondary`, `--small` | `type="success"` / `type="neutral"` + `size="small"` | Phase 4 |
| H5 | corporate-account | `corporate_account/application/list.html.twig` | 98 | `--{{ badge }}`, `--small` | dynamic `tag_type` from updated map | Phase 4 |
| H6 | corporate-account | `corporate_account/common/members_table.html.twig` | 21, 25 | `--success`, `--secondary`, `--small` | `type="success"` / `type="neutral"` + `size="small"` | Phase 4 |
| H7 | discounts | `discounts/status_badge.html.twig` | 10, 11, 12, 25 | `--success`, `--info`, `--warning` | `success` / `info` / `warning` | Phase 4 |
| H8 | discounts | `discounts/tab/products_list.html.twig` | 20 | `--info`, `--small` | `type="info"` + `size="small"` | Phase 4 |
| H9 | order-management | `order_management/component/status_badge.html.twig` | 4 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H10 | payment | `payment_method/status_badge.html.twig` | 3, 6, 10 | `--status`, `--success`, `--danger` | `success-ghost` / `error-ghost` | Phase 4 |
| H11 | payment | `payment_method/view.html.twig` | 53, 57 | `--status`, `--danger`, `--success` | `success-ghost` / `error-ghost` (via `tag_type`) | Phase 5 (uses page_title) |
| H12 | payment | `payment/view.html.twig` | 24 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H13 | payment | `payment/list.html.twig` | 76 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H14 | payment | `payment/order/summary.html.twig` | 23 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H15 | personalization | `personalization/models/list.html.twig` | 85, 87, 93 | `--success`, `--complementary`, `--{{ modifier }}` | `success`; `--complementary` **UNMAPPED**; dynamic map | Phase 4 |
| H16 | personalization | `personalization/models/edit.html.twig` | 99 | `--{{ modifier }}` | dynamic `tag_type` from updated map | Phase 4 |
| H17 | product-catalog | `product_catalog/catalog_macros.html.twig` | 3, 4, 11 | `--success`, `--warning` | `success` / `warning` | Phase 4 |
| H18 | product-catalog | `product_catalog/product_macros.html.twig` | 6, 14 | `--status`, `--success` | `success-ghost` / `neutral-ghost` | Phase 4 |
| H19 | product-catalog | `product_catalog/product/tab/variants.html.twig` | 93, 97 | `--danger`, `--success` | `error` / `success` | Phase 4 |
| H20 | product-catalog | `product_catalog/product/assets_collection.html.twig` | 38 | (none) | `type="neutral"` | Phase 4 |
| H21 | product-catalog | `product_catalog/product/view.html.twig` | 61 | `--info` (via `tag_extra_classes`) | `tag_type: 'info'` | Phase 5 (uses page_title) |
| H22 | scheduler | `ibexa_page_builder/page_builder/infobar/tables/versions.html.twig` | 8 | `--success` | `type="success"` | Phase 4 |
| H23 | shipping | `shipping/shipping_method/status_badge.html.twig` | 3, 6, 10 | `--status`, `--success`, `--danger` | `success-ghost` / `error-ghost` | Phase 4 |
| H24 | shipping | `shipping/shipping_method/view.html.twig` | 46, 50 | `--status`, `--danger`, `--success` | `success-ghost` / `error-ghost` (via `tag_type`) | Phase 5 (uses page_title) |
| H25 | shipping | `shipment/view.html.twig` | 24 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H26 | shipping | `shipment/list.html.twig` | 78 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H27 | shipping | `shipment/order/summary.html.twig` | 23 | `--custom-colors` | **UNMAPPED — keep legacy** | Skip |
| H28 | workflow | `ibexa_workflow/limitation/limitation_values.html.twig` | 3 | `--secondary` + inline style | `type="neutral"` + inline `style` kept as-is | Phase 4 |
| H29 | workflow | `ibexa_workflow/admin/content_view/tab/versions/table.html.twig` | 22 | `--secondary` + inline style | `type="neutral"` + inline `style` kept as-is | Phase 4 |
| H30 | workflow | `ibexa_workflow/admin/dashboard/table_workflow.html.twig` | 26 | `--secondary` + inline style | `type="neutral"` + inline `style` kept as-is | Phase 4 |

> **Note on workflow badges (H28–H30):** These use `ibexa-badge--secondary` for shape/base styling but override the color via inline `style="background-color: {{ stage.color }}"`. After migration, the inline `style` must be kept and passed through as an attribute on the DS tag component.

#### Group I: React badge usages — HOLD (no DS tag React component)

| # | Package | File | Line(s) | Modifiers | Notes |
|---|---------|------|---------|-----------|-------|
| I1 | admin-ui | `ui-dev/…/table.view.item.component.js` | 283–285 | `--status`, `--success` | Visibility status badge; keep legacy + TODO comment |
| I2 | product-catalog | `ui-dev/…/product.table.item.js` | 79 | `--info` | Product code; keep legacy + TODO comment |
| I3 | product-catalog | `ui-dev/…/product.variant.table.item.js` | 124 | `--info` | Variant code; keep legacy + TODO comment |
| I4 | product-catalog | `ui-dev/…/product.availability.status.js` | 21–22 | `--status`, `--success` | Availability status; keep legacy + TODO comment |
| I5 | product-catalog | `ui-dev/…/product.table.item.js` (PDW) | 108 | `--info` | Product code in discovery widget; keep legacy + TODO comment |
| I6 | product-catalog | `ui-dev/…/selected.items.product.item.description.js` | 5 | `--info` | Selected product code; keep legacy + TODO comment |

#### Group J: JS badge DOM manipulation (update when template migrates)

| # | Package | File | Line(s) | What |
|---|---------|------|---------|------|
| J1 | product-catalog | `js/catalog.edit.js` | 223–225 | Removes `.ibexa-badge--status:not(.ibexa-badge--success)` / `.ibexa-badge--status.ibexa-badge--success` nodes by CSS selector after injecting product row template |

#### Group K: Behat PHP locators (update in same commit as template migration)

| # | Package | File | Line | Selector |
|---|---------|------|------|---------|
| K1 | corporate-account | `Behat/Page/CompanyPage.php` | 95 | `.ibexa-badge` |
| K2 | discounts | `Behat/Page/DiscountPage.php` | 88 | `.ibexa-badge` |
| K3 | payment | `Behat/Page/PaymentMethodPage.php` | 91 | `.ibexa-badge` |
| K4 | product-catalog | `Behat/Page/CatalogPage.php` | 110 | `.ibexa-badge` |
| K5 | product-catalog | `Behat/Component/ProductAvailabilityTab.php` | 51–52 | `.ibexa-badge--status`, `ibexa-badge--success` |
| K6 | shipping | `Behat/Page/ShippingMethodPage.php` | 84 | `.ibexa-badge` |
| K7 | page-builder | `Behat/Component/Blocks/TargetingBlock.php` | 104 | `.ibexa-tag__content` |

#### Group L: SCSS badge context overrides (update when class changes)

| # | Package | File | What |
|---|---------|------|------|
| L1 | admin-ui | `scss/_badges.scss` | Root `.ibexa-badge` block definition |
| L2 | admin-ui | `scss/_page-title.scss` | `.ibexa-badge` context override (margin + font) |
| L3 | personalization | `scss/_model-edit.scss` | `.ibexa-badge` context override (margin + align) |

---

## Migration Order by Priority

### Phase 1: Simple Non-removable Inline Tags in admin-ui (3 files) — LOW RISK

No JS interaction. Pure template changes in admin-ui only.

1. **`admin-ui`** `account/profile/view.html.twig` (A1)
   - Role assignment loop tags → `<twig:ibexa:tag type="neutral">`
   - No JS, no data attributes, no companion classes

2. **`admin-ui`** `limitation/udw_limitation_value.html.twig` (A2)
   - Only the inline `{% else %}` branch "location deleted" tag → `<twig:ibexa:tag type="neutral">`
   - Do NOT touch the `include` calls (those go through `tag.html.twig`)

3. **`admin-ui`** `ui/field_type/preview/content_fields.html.twig` (A3, A4, A5 partial)
   - Multi-select `<li>`: move companion class to `<li>`, DS tag inside
   - Single-select: class injected via `attr|merge` — see Special Cases §5

4. **`admin-ui`** `ui/search/criteria_tags.html.twig` (A5)
   - Language filter inline tag only → `<twig:ibexa:tag type="neutral" class="ibexa-search-criteria-tags__tag">`
   - Leave all `search_tag.html.twig` include calls as-is

---

### Phase 2: Standalone Badges in admin-ui — LOW RISK

5. **`admin-ui`** `ui/tag.html.twig` (B1) — **TODO comment only, no migration**
   - Add: `{# TODO: Migrate to <twig:ibexa:chip> when DS chip supports a loading/spinner state #}`

---

### Phase 3: Inline Removable Tags in Other Packages — MEDIUM RISK

These are inline `ibexa-tag ibexa-tag--deletable` usages (no spinner) in non-admin-ui packages. Each requires a JS selector update in the same commit.

6. **`workflow`** `apply_transition_widget.html.twig` (D1) + `workflow.transition.apply.widget.js` (E5)
   - Tag → `<twig:ibexa:chip>`; JS: `.ibexa-tag__remove-btn--remove-reviewer` → `.ids-chip__delete`

7. **`personalization`** `models/form_fields.html.twig` (D2) + `model.edit.editorial.js` (E6)
   - Tag → `<twig:ibexa:chip>`; JS: `.ibexa-tag__remove-btn` → `.ids-chip__delete`
   - Note: tag is a Symfony form widget (`_model_editorContentList_entry_widget` block) rendered also as `data-template` at line 19 — the template string will need updating too

---

### Phase 4: Standalone Badges in Other Packages — MEDIUM RISK

All simple `ibexa-badge` standalone usages (not through `page_title.html.twig`). Each file is independent.

**`connector-ai`** (1 file):
8. `action_configuration/ui/status_badge.html.twig` (H2): `--status+--success/--danger` → `success-ghost` / `error-ghost`

**`corporate-account`** (4 files + Behat):
9. `individual/list.html.twig` (H3): `--info`/`--secondary`/`--small` → `type="info"`/`type="neutral"` + `size="small"`
10. `company/list.html.twig` (H4): `--success`/`--secondary`/`--small` → `type="success"`/`type="neutral"` + `size="small"`
11. `application/list.html.twig` (H5): dynamic `--{{ badge }}` → update `status_badge_map` values + `tag_type`
12. `common/members_table.html.twig` (H6): same as H4
    - **In same commit:** update `Behat/Page/CompanyPage.php` (K1)

**`discounts`** (2 files + Behat):
13. `status_badge.html.twig` (H7): `--success`/`--info`/`--warning` → `success`/`info`/`warning`
14. `tab/products_list.html.twig` (H8): `--info`/`--small` → `type="info"` + `size="small"`
    - **In same commit:** update `Behat/Page/DiscountPage.php` (K2)

**`payment`** (1 file + Behat — standalone only; view.html.twig deferred to Phase 5):
15. `payment_method/status_badge.html.twig` (H10): `--status+--success/--danger` → `success-ghost` / `error-ghost`
    - **In same commit:** update `Behat/Page/PaymentMethodPage.php` (K3)

**`personalization`** (2 files):
16. `models/list.html.twig` (H15): `--success` → `success`; `--{{ modifier }}` → update `states_colors_map`; `--complementary` → **UNMAPPED, keep legacy + TODO**
17. `models/edit.html.twig` (H16): `--{{ modifier }}` → update `states_colors_map`

**`product-catalog`** (4 Twig files + Behat + JS):
18. `catalog_macros.html.twig` (H17): `--success`/`--warning` → `success`/`warning`
19. `product_macros.html.twig` (H18): `--status+--success` → `success-ghost`; neutral fallback → `neutral-ghost`
20. `product/tab/variants.html.twig` (H19): `--danger`/`--success` → `error`/`success`
21. `product/assets_collection.html.twig` (H20): bare `ibexa-badge` → `type="neutral"`
    - **In same commit:** update `Behat/Page/CatalogPage.php` (K4), `Behat/Component/ProductAvailabilityTab.php` (K5), and `js/catalog.edit.js` (J1) selectors

**`scheduler`** (1 file):
22. `versions.html.twig` (H22): `--success` → `type="success"`

**`shipping`** (1 file + Behat — standalone only; view.html.twig deferred to Phase 5):
23. `shipping_method/status_badge.html.twig` (H23): `--status+--success/--danger` → `success-ghost` / `error-ghost`
    - **In same commit:** update `Behat/Page/ShippingMethodPage.php` (K6)

**`workflow`** (3 files):
24. `limitation/limitation_values.html.twig` (H28): `--secondary` + inline style → `type="neutral"` + `style` attribute passthrough
25. `admin/content_view/tab/versions/table.html.twig` (H29): same
26. `admin/dashboard/table_workflow.html.twig` (H30): same

---

### Phase 5: `page_title.html.twig` Block + All Cross-Package Callers — HIGH RISK (Coordinated)

This is a **cross-package coordinated change**. All files below must change in the same PR / release:

**`admin-ui`** (the base template):
27. `ui/page_title.html.twig` (H1): migrate `{% block tag %}` to `<twig:ibexa:tag :type="tag_type|default('neutral')">`

**Callers (other packages) — update `tag_extra_classes` → `tag_type`:**

| # | Package | File | Change |
|---|---------|------|--------|
| 28 | corporate-account | `company/details.html.twig` | `tag_extra_classes: 'ibexa-badge--success'` → `tag_type: 'success'` |
| 29 | product-catalog | `product/view.html.twig` | `tag_extra_classes: 'ibexa-badge--info'` → `tag_type: 'info'` |
| 30 | payment | `payment_method/view.html.twig` | `tag_extra_classes: badge_class` (was `'ibexa-badge--status ibexa-badge--danger/success'`) → `tag_type: 'error-ghost'`/`'success-ghost'`; also update local variable |
| 31 | payment | `payment/view.html.twig` | `tag_extra_classes: 'ibexa-badge--custom-colors'` → **UNMAPPED; keep `tag_extra_classes` as custom class passthrough** |
| 32 | shipping | `shipping_method/view.html.twig` | `tag_extra_classes: badge_class` (was `'ibexa-badge ibexa-badge--status ibexa-badge--...'`) → `tag_type: '...-ghost'`; strip `ibexa-badge` from class variable |
| 33 | shipping | `shipment/view.html.twig` | `tag_extra_classes: 'ibexa-badge--custom-colors'` → **UNMAPPED; keep `tag_extra_classes` as custom class passthrough** |

> **Note on unmapped callers (31, 33):** `payment/view.html.twig` and `shipping/shipment/view.html.twig` use `--custom-colors` which has no DS equivalent. After migrating `page_title.html.twig`, these two files will keep `tag_extra_classes` but it will be passed as a `class` prop on the DS tag rather than as the badge type — the custom-color CSS will still function as long as the SCSS context rule is updated from `.ibexa-badge` to `.ids-tag`.

---

### Phase 6: `search_tag.html.twig` + JS — BLOCKED (Needs DS Investigation)

**Blocker:** The DS chip's internal delete button (`ids-chip__delete`) is rendered inside the component template as `<twig:ibexa:button>`. Verify whether arbitrary `data-*` attributes can be passed to it before proceeding.

34. `ui/search_tag.html.twig` — blocked
35. `js/scripts/admin.search.filters.js` — update in same commit as #34

---

### Phase 7: `tag.html.twig` Full Migration + All Callers — BLOCKED (Spinner)

**Blocker:** DS chip needs spinner/loading state support.

When unblocked, migrate in this order:
36. `ui/tag.html.twig` (B1)
37–42. All C-group callers (C1–C9) + corresponding JS files (E1–E4, E7–E10)
43. React `Tag` component (F1) and its consumers (F2, F3) — needs DS chip React component

---

### Permanently Deferred (Unmapped)

These items have no DS equivalent and should keep legacy CSS with TODO comments:

- All `--custom-colors` usages: H9, H12, H13, H14, H25, H26, H27
- All `--complementary` usages: H15 (partially)
- React components I1–I6 (no DS tag React component yet)

---

## JavaScript Selector Updates

### Selector Mapping

| Legacy Selector | DS Selector | Component |
|---|---|---|
| `.ibexa-tag` | `.ids-chip` | Chip container |
| `.ibexa-tag__content` | `.ids-chip__content` | Chip text content |
| `.ibexa-tag__remove-btn` | `.ids-chip__delete` | Delete button |
| `.ibexa-tag__spinner` | **No equivalent** | Spinner — not migrated |
| `.ibexa-tag--deletable` | `.ids-chip` | No modifier needed |
| `.ibexa-badge` | `.ids-tag` | Tag container |
| `.ibexa-badge--success` | `.ids-tag--success` | (when querying by type) |
| `.ibexa-badge--status` | `.ids-tag--success-ghost` / `.ids-tag--error-ghost` | Ghost type tags |

### Phase 3 JS Updates (inline removable tags — do in same commit as template)

**`workflow/workflow.transition.apply.widget.js`** (E5):
```js
❌ BEFORE (line 14):
const removeReviewerButtons = doc.querySelectorAll('.ibexa-tag__remove-btn--remove-reviewer');

✅ AFTER:
// Note: DS chip has a single .ids-chip__delete; identity via closest() to parent
const removeReviewerButtons = doc.querySelectorAll(
    '.ibexa-workflow-apply-transition__selected-user .ids-chip__delete'
);
```

**`personalization/model.edit.editorial.js`** (E6):
```js
❌ BEFORE (lines 37, 68):
insertedTag.querySelector('.ibexa-tag__remove-btn').addEventListener(...)
editorialModels.querySelectorAll('.ibexa-tag__remove-btn').forEach(...)

✅ AFTER:
insertedTag.querySelector('.ids-chip__delete').addEventListener(...)
editorialModels.querySelectorAll('.ids-chip__delete').forEach(...)
```

### Phase 7 JS Updates (blocked — listed for reference)

When `tag.html.twig` is migrated, update in the same commit:

- `admin.limitation.pick.js`: `.ibexa-tag` → `.ids-chip`; `__content` → `.ids-chip__content`; `__remove-btn` → `.ids-chip__delete`; `__spinner` → no equivalent (handle separately)
- `udw/select.location.js`: `__content`, `__spinner`, `__remove-btn` → DS equivalents
- `admin.contenttype.relation.default.location.js`: `__remove-btn` → `.ids-chip__delete`
- `personalization/scenarios.preview.js`: `__remove-btn` → `.ids-chip__delete`
- `personalization/category.path.select.js`: `.ibexa-tag` → `.ids-chip`; `__remove-btn` → `.ids-chip__delete`
- `segmentation/targeted.content.map.js`: `.ibexa-tag` → `.ids-chip`; `__content` → `.ids-chip__content`; `__remove-btn` → `.ids-chip__delete`
- `product-catalog/base.filter.config.js`: `__remove-btn` → `.ids-chip__delete`

### Phase 4 JS Updates (badges — product-catalog catalog.edit.js)

**`product-catalog/catalog.edit.js`** (J1):
```js
❌ BEFORE (lines 223-225):
container.querySelector('.ibexa-badge--status:not(.ibexa-badge--success)').remove();
container.querySelector('.ibexa-badge--status.ibexa-badge--success').remove();

✅ AFTER:
container.querySelector('.ids-tag--neutral-ghost, .ids-tag--error-ghost').remove();
container.querySelector('.ids-tag--success-ghost').remove();
```

---

## Per-File Migration Checklist

### Before Migration

- [ ] Read the file fully to understand context
- [ ] Determine if `ibexa-tag` is removable or non-removable
- [ ] Map all `ibexa-badge` modifiers to DS tag `type`
- [ ] List any JS files that target this template's `.ibexa-tag*` or `.ibexa-badge*` selectors
- [ ] Check for companion CSS classes that must be preserved via `class` prop
- [ ] Check for `data-*` attributes that need passthrough
- [ ] Identify `{% for %}` loops producing multiple tags
- [ ] Check for Behat PHP locators referencing these selectors
- [ ] For cross-package changes: confirm all callers are included in the same PR

### During Migration

- [ ] Non-removable `ibexa-tag` → `<twig:ibexa:tag type="neutral">`
- [ ] Removable inline `ibexa-tag` (no spinner) → `<twig:ibexa:chip>`
- [ ] `ibexa-badge` → `<twig:ibexa:tag>` with mapped `type` prop
- [ ] `ibexa-badge--status + --success` → `type="success-ghost"`
- [ ] `ibexa-badge--status + --danger` → `type="error-ghost"`
- [ ] `ibexa-badge--small` → `size="small"`
- [ ] `ibexa-badge--danger` → `type="error"` (renamed)
- [ ] `ibexa-badge--secondary` → `type="neutral"` (renamed)
- [ ] Preserve companion classes via `class` prop
- [ ] Preserve `data-*` attributes via Symfony UX attribute passthrough
- [ ] Add TODO comment for unmapped modifiers (`--complementary`, `--custom-colors`)
- [ ] Update JS selectors in same commit when removable tags are migrated
- [ ] Update Behat PHP locators in same commit

### After Migration

- [ ] Lint: `php bin/console lint:twig src/bundle/Resources/views/themes/admin/[file]`
- [ ] Clear cache: `rm -rf var/cache/dev && php bin/console cache:clear --no-warmup`
- [ ] Visual check in browser
- [ ] Tags/badges render correctly with correct colors
- [ ] Remove button works (for chips)
- [ ] JS interactions unbroken
- [ ] Run: `composer test`

### Commit Message Format

```
fix: Migrate [package]/[short-filename] ibexa-tag/ibexa-badge to design-system-twig

- Replace [N] non-removable ibexa-tag with <twig:ibexa:tag>
- Replace [N] ibexa-badge--[modifier] with <twig:ibexa:tag type="[type]">
[- Replace [N] removable ibexa-tag with <twig:ibexa:chip>]
[- Update .ibexa-tag__remove-btn selector in [js-file] to .ids-chip__delete]
[- Update .ibexa-badge selector in [behat-file] to .ids-tag]
[- Add TODO comment for [unmapped-modifier] — no DS equivalent yet]

Refs: TAG_BADGE_MIGRATION_PLAN.md
```

---

## Special Cases & Warnings

### 1. `tag.html.twig` — Spinner State Blocks Full Migration

**File:** `admin-ui/ui/tag.html.twig`

The file has `is_loading_state` / `ibexa-tag__spinner` which toggle a spinner SVG. DS chip has no spinner support. **Keep as legacy.**

Add TODO comment at top of file:
```twig
{# TODO: Migrate to <twig:ibexa:chip> when DS chip supports a loading/spinner state #}
{# Legacy removable tag template — kept as-is per TAG_BADGE_MIGRATION_PLAN.md #}
```

---

### 2. `search_tag.html.twig` — `data-target-selector` on Remove Button

**File:** `admin-ui/ui/search_tag.html.twig`

The remove button carries `data-target-selector="{{ target_selector }}"` — used by `admin.search.filters.js` to find and clear the associated form input. DS chip's delete button is rendered internally. Verify whether `data-*` attributes can be forwarded to it before migrating.

---

### 3. `page_title.html.twig` — Cross-Package Breaking Change

Migrating `{% block tag %}` requires simultaneous updates to 6 templates in 4 other packages. The `tag_extra_classes` variable is preserved as a `class` prop passthrough for non-type purposes (e.g. `--custom-colors`). The `tag_type` variable is the new way to control the DS tag type.

---

### 4. `content_fields.html.twig` — Single-Select `attr|merge` Class Injection

**File:** `admin-ui/ui/field_type/preview/content_fields.html.twig`, line 161

```twig
{% set attr = attr|merge({'class': (attr.class|default('') ~ ' ibexa-field-preview ibexa-field-preview--ibexa_selection-single ibexa-tag')|trim}) %}
```

This injects `ibexa-tag` into a dynamic attribute dict, then applies it to `{{ block('field_attributes') }}`. The DS tag is a standalone component (renders its own `<div>`), so it cannot receive `block('field_attributes')` attributes directly via `attr`. **Option:** Wrap the block content in a DS tag component and move the block attributes to a wrapper element. Needs careful testing.

---

### 5. `data-template` Pattern (Complex — Multiple Files)

Several patterns pre-render `tag.html.twig` into a `data-template` attribute for JS injection:
- `admin-ui/content_type/relation_list_form_fields.html.twig` (line 32)
- `admin-ui/content_type/relation_form_fields.html.twig` (line 32)
- `product-catalog/list_filter_tags.html.twig`
- `segmentation/segmentation.html.twig` (via `segmentation_item.html.twig`)

DS chip components require server-side Twig rendering and cannot be pre-rendered into a `data-template` string for client-side injection using simple string replacement. These are **blocked by the same spinner blocker as Phase 7** — they require both DS chip spinner support and a rethink of the JS injection pattern.

---

### 6. `personalization/models/form_fields.html.twig` — Symfony Form Widget Block

The `ibexa-tag` at lines 31–41 is inside a Symfony form widget block (`_model_editorContentList_entry_widget`). The entire block renders as a string via `form_widget(form.vars.prototype)` and is stored in `data-template` at line 19. Migration to DS chip here requires the same approach rethink as Special Case §5.

---

### 7. `workflow` Badges with Inline Style Color Override

**Files:** H28, H29, H30 in workflow package.

These use `ibexa-badge--secondary` for the badge shape but override the color via `style="background-color: {{ stage.color }}"`. After migration to `<twig:ibexa:tag type="neutral">`, the inline `style` must be preserved:

```twig
✅ AFTER:
<twig:ibexa:tag type="neutral" style="background-color: {{ stage.color }}">
    {{ workflow_name }}: {{ stage_name }}
</twig:ibexa:tag>
```

---

### 9. `content_fields.html.twig` — Single-Select `attr|merge` Class Injection (Detailed Analysis)

**File:** `admin-ui/ui/field_type/preview/content_fields.html.twig`, lines 161–167

The single-select branch has an inverted nesting pattern that requires careful reading:

```twig
{% set attr = attr|merge({'class': (attr.class|default('') ~ ' ibexa-field-preview ibexa-field-preview--ibexa_selection-single ibexa-tag')|trim}) %}

{% set field_value = options[field.value.selection|first]|escape %}

<div class="ibexa-tag__content">
    {{ block( 'simple_block_field' ) }}
</div>
```

**What actually renders (tracing the block chain):**

1. `attr|merge` adds `ibexa-tag` (plus preview classes) into the `attr` dict's `class`
2. `block('simple_block_field')` is called — defined at line 485:
   ```twig
   {% block simple_block_field %}
       <div {{ block( 'field_attributes' ) }}>{{ field_value|raw }}</div>
   {% endblock %}
   ```
3. `block('field_attributes')` (line 502) iterates over `attr` and renders key/value pairs
4. Result: `<div class="... ibexa-field-preview ibexa-field-preview--ibexa_selection-single ibexa-tag">value</div>`
5. That `<div ibexa-tag>` is the **inner** div, wrapped by `<div class="ibexa-tag__content">`

So the structure is **backwards from normal**: `ibexa-tag` is on the inner rendered div, `ibexa-tag__content` is on the outer literal div. This is a quirky but intentional rendering pattern.

**Migration approach:**

- Remove `ibexa-tag` from the `attr|merge` class string (keep the preview classes)
- Remove the `<div class="ibexa-tag__content">` wrapper
- Wrap `{{ block('simple_block_field') }}` in `<twig:ibexa:tag type="neutral">`
- Result: `<twig:ibexa:tag type="neutral"><div class="ibexa-field-preview ...">value</div></twig:ibexa:tag>`

```twig
❌ BEFORE:
{% set attr = attr|merge({'class': (attr.class|default('') ~ ' ibexa-field-preview ibexa-field-preview--ibexa_selection-single ibexa-tag')|trim}) %}

{% set field_value = options[field.value.selection|first]|escape %}

<div class="ibexa-tag__content">
    {{ block( 'simple_block_field' ) }}
</div>

✅ AFTER:
{% set attr = attr|merge({'class': (attr.class|default('') ~ ' ibexa-field-preview ibexa-field-preview--ibexa_selection-single')|trim}) %}

{% set field_value = options[field.value.selection|first]|escape %}

<twig:ibexa:tag type="neutral">
    {{ block( 'simple_block_field' ) }}
</twig:ibexa:tag>
```

---

### 8. React Components — No DS Tag/Chip React Component Available

All React usages (I1–I6, F1–F3) remain as legacy. There is currently no `@ids-components/components/Tag` or `@ids-components/components/Chip` React component exposed.

Add TODO comments:
```jsx
{/* TODO: Migrate to DS tag/chip React component when available */}
{/* Legacy ibexa-badge--status pattern — maps to DS success-ghost type */}
```

---

## Style Guide Compliance

### DO:

- Use `type="neutral"` as the default when no color modifier is present
- Map `--danger` → `type="error"` and `--secondary` → `type="neutral"`
- Map `--small` → `size="small"`
- Pass companion classes via `class` prop
- Pass `data-*` attributes directly on the component (Symfony UX auto-collects them)
- Use `:isDeletable="false"` to make a chip non-removable
- Use `type="success-ghost"` / `type="error-ghost"` for status dot indicators
- Add TODO comments for unmapped patterns
- Update JS selectors and Behat locators in the same commit as the template

### DON'T:

- Use `<twig:ibexa:badge>` for colored text pills (DS badge is a number counter)
- Migrate `tag.html.twig` itself (spinner state not supported)
- Migrate `ibexa-taggify` or `ibexa-tag-view-select` (out of scope)
- Forget to update JS selectors in the same commit when migrating removable tags
- Use `<twig:ibexa:chip>` for non-removable display labels (use `<twig:ibexa:tag>` instead)
- Migrate `--custom-colors` usages (no DS equivalent)
- Change `page_title.html.twig` without updating all 6 downstream callers simultaneously

---

## Validation Commands

```bash
# Lint Twig syntax
php bin/console lint:twig src/bundle/Resources/views/themes/admin/[path-to-file]

# Run all PHP tests
composer test

# Code style check
composer check-cs

# Static analysis
composer phpstan

# Frontend checks
yarn test

# Track migration progress — remaining ibexa-tag in admin-ui
grep -r "ibexa-tag" src/bundle/Resources/views/ --include="*.twig" | grep -v "taggify\|tag-view-select"
grep -r "ibexa-tag" src/bundle/ui-dev/ --include="*.js" | grep -v "taggify\|tag-view-select"

# Track migration progress — remaining ibexa-badge in admin-ui
grep -r "ibexa-badge" src/bundle/Resources/views/ --include="*.twig"
grep -r "ibexa-badge" src/bundle/ui-dev/ --include="*.js"

# Cross-package — remaining ibexa-badge across all packages
grep -r "ibexa-badge" vendor/ibexa/ --include="*.twig" | grep -v "admin-ui"
```

---

## Testing Strategy

### Required Testing Approach

**1. Twig Syntax Validation**
```bash
php bin/console lint:twig vendor/ibexa/[package]/src/bundle/Resources/views/[file].html.twig
```

**2. Manual Browser Testing — REQUIRED**
```bash
rm -rf var/cache/dev && php bin/console cache:clear --no-warmup
```

For each migrated file verify:
- Tags/badges render with correct visual appearance
- Color variants match the original (info = blue, success = green, error = red, etc.)
- Ghost types show the colored dot indicator (for `--status` pattern)
- Non-removable tags have NO delete button
- Removable chips show the ✕ delete button and clicking works
- JS interactions unbroken (removal, state management)
- Companion CSS classes still apply (layout/spacing context)
- `data-*` attributes present in DOM
- Behat tests pass (run before and after)

**3. Check Application Logs**
```bash
tail -50 var/log/dev.log | grep -i "exception\|error"
```

### Common Issues to Watch For

**1. Type prop validation error**
```
Error: The option "type" with value "danger" is invalid.
Cause: Used legacy color name. ibexa-badge--danger maps to type="error".
Fix: See type mapping table.
```

**2. JS interaction broken after chip migration**
```
Problem: Clicking delete button does nothing.
Cause: JS still queries .ibexa-tag__remove-btn but HTML now has .ids-chip__delete.
Fix: Update JS selector in same commit as template.
```

**3. Wrong DS component used**
```
Problem: Tag renders with unexpected delete button, or chip without one.
Fix: Removable → chip; non-removable → tag.
```

**4. Companion CSS classes lost**
```
Problem: Layout/spacing wrong in context.
Fix: Pass context class via class prop.
```

**5. `<twig:ibexa:badge>` used accidentally**
```
Problem: Renders as a numeric counter instead of colored text pill.
Fix: Replace with <twig:ibexa:tag type="...">.
```

---

## Progress Tracking

**Total items in scope:**
- Phase 1: 4 admin-ui template changes — simple non-removable inline tags
- Phase 2: 1 TODO comment
- Phase 3: 2 packages (workflow, personalization) — inline removable tags + JS
- Phase 4: 10 packages — standalone badges (Twig only, no cross-package dep)
- Phase 5: 1 admin-ui template + 6 cross-package callers — coordinated
- Phase 6: Blocked (DS investigation needed)
- Phase 7: Blocked (DS spinner support needed)

**Completed:** 0

### Phase Completion

- [ ] **Phase 1** — Simple non-removable inline tags in admin-ui (0/4)
  - [ ] `account/profile/view.html.twig`
  - [ ] `limitation/udw_limitation_value.html.twig` (inline tag only)
  - [ ] `ui/field_type/preview/content_fields.html.twig`
  - [ ] `ui/search/criteria_tags.html.twig` (language tag only)

- [ ] **Phase 2** — TODO comment on `tag.html.twig` (0/1)
  - [ ] `ui/tag.html.twig`

- [ ] **Phase 3** — Inline removable tags in workflow + personalization (0/2 templates + 2 JS)
  - [ ] `workflow/apply_transition_widget.html.twig` + `workflow.transition.apply.widget.js`
  - [ ] `personalization/models/form_fields.html.twig` + `model.edit.editorial.js`

- [ ] **Phase 4** — Standalone badges in other packages (0/26 items)
  - [ ] connector-ai: `status_badge.html.twig`
  - [ ] corporate-account: `individual/list.html.twig`, `company/list.html.twig`, `application/list.html.twig`, `members_table.html.twig` + Behat K1
  - [ ] discounts: `status_badge.html.twig`, `products_list.html.twig` + Behat K2
  - [ ] payment: `payment_method/status_badge.html.twig` + Behat K3
  - [ ] personalization: `models/list.html.twig`, `models/edit.html.twig`
  - [ ] product-catalog: `catalog_macros.html.twig`, `product_macros.html.twig`, `variants.html.twig`, `assets_collection.html.twig` + Behat K4+K5 + `catalog.edit.js` J1
  - [ ] scheduler: `versions.html.twig`
  - [ ] shipping: `shipping_method/status_badge.html.twig` + Behat K6
  - [ ] workflow: `limitation_values.html.twig`, `versions/table.html.twig`, `table_workflow.html.twig`

- [ ] **Phase 5** — `page_title.html.twig` cross-package coordinated (0/7 files)
  - [ ] `admin-ui/ui/page_title.html.twig`
  - [ ] `corporate-account/company/details.html.twig`
  - [ ] `product-catalog/product/view.html.twig`
  - [ ] `payment/payment_method/view.html.twig`
  - [ ] `payment/payment/view.html.twig` (keep `--custom-colors` as class passthrough)
  - [ ] `shipping/shipping_method/view.html.twig`
  - [ ] `shipping/shipment/view.html.twig` (keep `--custom-colors` as class passthrough)

- [ ] **Phase 6** — `search_tag.html.twig` + JS (BLOCKED — DS chip investigation needed)

- [ ] **Phase 7** — `tag.html.twig` full migration + callers (BLOCKED — DS spinner support needed)

---

## Quick Reference

### Component Selection Rule

```
Has a remove button?
  YES, no spinner → <twig:ibexa:chip>
  YES, has spinner → HOLD (tag.html.twig blocker)
  NO  → <twig:ibexa:tag>

ibexa-badge is always non-removable → always <twig:ibexa:tag>
```

### Type Quick Map

```
ibexa-tag (no color)           → type="neutral"
ibexa-badge (no color)         → type="neutral"
ibexa-badge--primary           → type="primary"
ibexa-badge--info              → type="info"
ibexa-badge--warning           → type="warning"
ibexa-badge--success           → type="success"
ibexa-badge--danger            → type="error"         ← renamed
ibexa-badge--secondary         → type="neutral"       ← renamed
ibexa-badge--small             → size="small"
ibexa-badge--status + success  → type="success-ghost"
ibexa-badge--status + danger   → type="error-ghost"
ibexa-badge--status (no color) → type="neutral-ghost"
ibexa-badge--complementary     → UNMAPPED (TODO comment)
ibexa-badge--custom-colors     → UNMAPPED (TODO comment)
```

---

**Plan Status**: In Progress
**Plan Version**: 2.1
**Date**: March 10, 2026
**Next Step**: Phase 1 — 4 simple non-removable inline tag files in admin-ui

---

## Execution Decisions (Session March 10, 2026)

### Git Strategy

- **Tags branch:** `IBX-11416-tag-ds` — all `<twig:ibexa:tag>` migrations (Phases 1, 2, 4, 5)
- **Chips branch:** `IBX-11417-chip-ds` — all `<twig:ibexa:chip>` migrations (Phases 3, 6, 7)
- **Commit granularity:** one commit per file (finest granularity — easiest to revert)
- **Scope in progress:** Phase 1 complete on `IBX-11416-tag-ds`

### Phase 1 Commit Order

| # | File | Commit description |
|---|------|--------------------|
| 1 | `account/profile/view.html.twig` | Role assignment loop: `ibexa-tag` → `<twig:ibexa:tag type="neutral">` |
| 2 | `limitation/udw_limitation_value.html.twig` | `{% else %}` branch inline tag only → `<twig:ibexa:tag type="neutral">` |
| 3 | `ui/field_type/preview/content_fields.html.twig` | Multi-select `<li>` class restructure + single-select `attr|merge` rework |
| 4 | `ui/search/criteria_tags.html.twig` | Language filter inline tag → `<twig:ibexa:tag type="neutral" class="...">` |
