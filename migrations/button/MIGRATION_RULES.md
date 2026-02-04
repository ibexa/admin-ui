# Button Migration Rules: Legacy Twig → Twig Component

This document outlines the migration pattern from legacy button markup to the new `twig:ibexa:button` component.

---

## ⚠️ IMPORTANT: Git Workflow Policy

**FOR AI ASSISTANTS (Claude, OpenAI Codex, etc.):**

All changes MUST remain **local only**. Do NOT push changes or create pull requests automatically.

- ❌ **NEVER** run `git push`
- ❌ **NEVER** run `gh pr create` or similar PR commands  
- ❌ **NEVER** push to remote repositories
- ✅ **ONLY** make local changes, commits, and branches
- ✅ **ALWAYS** let the user manually push and create PRs

See **Section 4: Step 4 - Git Workflow** for complete details.

---

## Quick Reference

**Component Location:**
```
vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/button.html.twig
```

**Basic Migration:**
```twig
<!-- OLD -->
<button type="button" class="btn ibexa-btn ibexa-btn--primary">Submit</button>

<!-- NEW -->
<twig:ibexa:button type="primary">Submit</twig:ibexa:button>
```

**Supported Variants:** `primary`, `secondary`, `tertiary`, `secondary-alt`, `tertiary-alt`
**Supported Sizes:** `small`, `medium` (default)
**Key Props:** `type`, `size`, `icon`, `icon_size`, `html_type`, `:disabled`, `class`

---

## 1. PATTERN: Transformation Rules

### Basic Button Structure

**OLD (Legacy):**
```twig
<button
    type="button"
    class="btn ibexa-btn ibexa-btn--{variant}"
    disabled
>
    <svg class="ibexa-icon ibexa-icon--{size} ibexa-icon--{name}">
        <use xlink:href="{{ ibexa_icon_path('{name}') }}"></use>
    </svg>
    <span class="ibexa-btn__label">
        {{ 'button.text'|trans }}
    </span>
</button>
```

**NEW (Component):**
```twig
<twig:ibexa:button
    type="{mapped_variant}"
    icon="{name}"
    icon_size="{size}"
    :disabled="true"
>
    {{ 'button.text'|trans }}
</twig:ibexa:button>
```

### Variant Mapping

| Legacy Class | Component Type | Notes |
|--------------|----------------|-------|
| `ibexa-btn--ghost` | `tertiary` | Ghost style (transparent background) |
| `ibexa-btn--primary` | `primary` | Primary action button |
| `ibexa-btn--secondary` | `secondary` | Secondary action button |
| `ibexa-btn--tertiary` | `tertiary-alt` | Tertiary action button |

**Additional Legacy Variants (Not Yet Mapped):**

These legacy variants do NOT have direct component equivalents. Keep them as legacy buttons with TODO comments until the design system component is extended:

- `ibexa-btn--ghost-info` - Ghost button with info color
- `ibexa-btn--dark` - Dark background button
- `ibexa-btn--filled-info` - Filled info button
- `ibexa-btn--info` - Info styled button
- `ibexa-btn--neon-info` - Neon info button
- `ibexa-btn--selector` - Selector button variant
- `ibexa-btn--dark-selector` - Dark selector button
- `ibexa-btn--secondary-light` - Light secondary button
- `ibexa-btn--secondary-dark` - Dark secondary button

**Usage:** If you encounter buttons with these classes, leave them as-is and add a TODO comment.

**Finding All Button Variants in Codebase:**

To discover all button class patterns currently used:
```bash
git grep -h "ibexa-btn--" | grep -oE "ibexa-btn--[a-z-]+" | sort -u
```

This helps identify which buttons can be migrated vs. which need special handling.

### Icon Mapping

**OLD:**
```twig
<svg class="ibexa-icon ibexa-icon--small-medium ibexa-icon--edit">
    <use xlink:href="{{ ibexa_icon_path('edit') }}"></use>
</svg>
```

**NEW:**
```twig
icon="edit"
icon_size="small-medium"
```

### Icon-Only Buttons

Buttons with only an icon (no text) are automatically detected by the component.

**OLD:**
```twig
<button class="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text" type="button">
    <svg class="ibexa-icon ibexa-icon--small-medium ibexa-icon--edit">
        <use xlink:href="{{ ibexa_icon_path('edit') }}"></use>
    </svg>
</button>
```

**NEW:**
```twig
<twig:ibexa:button
    type="tertiary"
    icon="edit"
    icon_size="small-medium"
/>
```

**Note:** Remove the `ibexa-btn--no-text` class - the component automatically detects icon-only buttons when no text content is provided.

### Button Size Mapping

The design system button component supports two size variants:

| Legacy Class | Component Prop | Notes |
|--------------|----------------|-------|
| `ibexa-btn--small` | `size="small"` | Small button (32px height) |
| (default) | `size="medium"` or omit | Default/medium size (46px height) |

**Important Notes:**
- The component's default size is `medium` (can be omitted)
- Legacy code without `ibexa-btn--small` should NOT specify a size prop
- The component does NOT support a `large` size variant

**Component Size Variants (from design-system-twig):**
```twig
size: {
    medium: 'ids-btn--medium',
    small: 'ids-btn--small',
}
```

### HTML Type Attribute

| Legacy Attribute | Component Prop | Notes |
|------------------|----------------|-------|
| `type="button"` | (default, omit) | Default button type |
| `type="submit"` | `html_type="submit"` | Form submit button |
| `type="reset"` | `html_type="reset"` | Form reset button |

---

## Component Props Reference

### Available Props (from design-system-twig)

```twig
<twig:ibexa:button
    type="primary|secondary|tertiary|secondary-alt|tertiary-alt"
    size="small|medium"
    icon="icon-name"
    icon_size="small|medium|small-medium|..."
    html_type="button|submit|reset"
    :disabled="true|false"
    class="custom-classes"
    id="button-id"
    data-*="any-data-attribute"
    {any-other-html-attribute}
>
    Button Label
</twig:ibexa:button>
```

### Prop Details

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | string | - | Button variant: `primary`, `secondary`, `tertiary`, `secondary-alt`, `tertiary-alt` |
| `size` | string | `medium` | Button size: `small` or `medium` |
| `icon` | string | - | Icon name (renders using `twig:ibexa:icon`) |
| `icon_size` | string | - | Icon size passed to icon component |
| `html_type` | string | `button` | HTML button type attribute |
| `disabled` | boolean | `false` | Disabled state (use `:disabled` for boolean) |
| `class` | string | - | Additional CSS classes (appended to component classes) |
| `attributes` | object | - | All other attributes passed through to `<button>` element |

### Component Behavior

1. **Icon-Only Detection**: If no text content and `icon` prop is set, the button automatically becomes icon-only
2. **Disabled Class**: When `:disabled="true"`, the component adds both `disabled` attribute and `ids-btn--disabled` class
3. **Label Wrapping**: Text content is wrapped in `<div class="ids-btn__label">`
4. **Icon Wrapping**: Icon is wrapped in `<div class="ids-btn__icon">`
5. **Class Merging**: Custom classes from `class` prop are appended to component's base classes

---

## Special Cases and Edge Cases

### Buttons with Multiple Icons

If a legacy button has multiple icons or complex icon structures, the component may not support it directly. Consider:

1. Leave as legacy button with TODO comment
2. Request component enhancement in design-system-twig
3. Use custom markup with component classes

### Anchor Tags Styled as Buttons

**OLD:**
```twig
<a href="{{ path }}" class="btn ibexa-btn ibexa-btn--primary">
    Link Button
</a>
```

**Current Component:** The button component renders a `<button>` element, NOT an `<a>` tag.

**Solution:** Keep anchor tags as-is until a link button component is available, or wrap the component in an anchor.

### Form Widgets

When migrating Symfony form widgets that render buttons, ensure form attributes are preserved:

**OLD:**
```twig
{{ form_widget(form.submit, {'attr': {'class': 'btn ibexa-btn ibexa-btn--primary'}}) }}
```

**NEW:** May require form theme customization or manual rendering. Consider keeping form widgets as-is initially.

### Disabled State with Dynamic Logic

**OLD:**
```twig
<button class="btn ibexa-btn ibexa-btn--primary" {{ condition ? 'disabled' : '' }}>
```

**NEW:**
```twig
<twig:ibexa:button
    type="primary"
    :disabled="condition"
>
```

**Important:** Use `:disabled` (with colon) for boolean expressions.

---

## 2. JS HANDOFF: Maintaining Functionality

### Data Attributes
**All data attributes are preserved directly:**

**OLD:**
```twig
<button
    data-content-id="{{ contentId }}"
    data-language-code="{{ language }}"
    data-bs-toggle="modal"
    data-bs-target="#modal-id"
>
```

**NEW:**
```twig
<twig:ibexa:button
    data-content-id="{{ contentId }}"
    data-language-code="{{ language }}"
    data-bs-toggle="modal"
    data-bs-target="#modal-id"
>
```

### Custom CSS Classes
**Preserve all custom classes via the `class` attribute:**

**OLD:**
```twig
<button class="btn ibexa-btn ibexa-btn--ghost mx-2 ibexa-btn--content-edit">
```

**NEW:**
```twig
<twig:ibexa:button
    type="tertiary"
    class="mx-2 ibexa-btn--content-edit"
>
```

**Note:** Remove `btn`, `ibexa-btn`, and variant classes (`ibexa-btn--ghost`, etc.). Keep only custom/functional classes.

### JS Selector Updates

If JavaScript relies on the `disabled` attribute, the component handles it automatically:

**OLD JS:**
```javascript
applyBtn.setAttribute('disabled', true);
applyBtn.removeAttribute('disabled');
```

**NEW JS (IDS Component requires additional class):**
```javascript
applyBtn.setAttribute('disabled', true);
applyBtn.classList.add('ids-btn--disabled');

applyBtn.removeAttribute('disabled');
applyBtn.classList.remove('ids-btn--disabled');
```

**Example from `admin.search.filters.js`:**
```javascript
const methodName = isEnabled ? 'removeAttribute' : 'setAttribute';
applyBtn[methodName]('disabled', !isEnabled);
applyBtn.classList.toggle('ids-btn--disabled', !isEnabled);  // NEW LINE
```

### ID Attributes
**IDs are preserved:**

**OLD:**
```twig
<button id="confirm-{{ form.remove.vars.id }}">
```

**NEW:**
```twig
<twig:ibexa:button id="confirm-{{ form.remove.vars.id }}">
```

---

## 3. BC: Backward Compatibility Rules

### Core BC Principle
**The component MUST render with legacy classes to ensure existing CSS and JS selectors continue working.**

The component renders BOTH IDS classes (for new design system) AND legacy ibexa classes (for backward compatibility).

### Required Legacy Classes on Output

The component internally renders:
```html
<button class="ids-btn ids-btn--{ids-variant} btn ibexa-btn ibexa-btn--{legacy-variant} {custom_classes}">
```

**Variant Mapping in Rendered Output:**

| Component Type | IDS Classes | Legacy BC Classes |
|----------------|-------------|-------------------|
| `primary` | `ids-btn--primary` | `btn ibexa-btn ibexa-btn--primary` |
| `secondary` | `ids-btn--secondary` | `btn ibexa-btn ibexa-btn--secondary` |
| `tertiary` | `ids-btn--tertiary` | `btn ibexa-btn ibexa-btn--ghost` |
| `secondary-alt` | `ids-btn--secondary-alt` | `btn ibexa-btn ibexa-btn--secondary` |
| `tertiary-alt` | `ids-btn--tertiary-alt` | `btn ibexa-btn ibexa-btn--tertiary` |

**Size Classes:**

| Component Size | IDS Classes | Legacy BC Classes |
|----------------|-------------|-------------------|
| `small` | `ids-btn--small` | `ibexa-btn--small` |
| `medium` | `ids-btn--medium` | *(default, no class)* |

**Example for `type="tertiary"` + `size="small"`:**
```html
<button class="ids-btn ids-btn--tertiary ids-btn--small btn ibexa-btn ibexa-btn--ghost ibexa-btn--small">
```

### Custom Class Preservation
Any classes passed via the `class` attribute are **appended** to the base classes:

**Component:**
```twig
<twig:ibexa:button type="tertiary" class="mx-2 ibexa-btn--content-edit">
```

**Rendered:**
```html
<button class="btn ibexa-btn ibexa-btn--ghost mx-2 ibexa-btn--content-edit">
```

### Attribute Passthrough
All non-component attributes (data-*, id, title, etc.) are passed through to the underlying `<button>` element.

### Disabled State
The `:disabled="true"` prop renders both:
1. The `disabled` HTML attribute
2. The `ids-btn--disabled` CSS class (for IDS styling)

**Rendered:**
```html
<button class="btn ibexa-btn ibexa-btn--ghost ids-btn--disabled" disabled>
```

### Rendered HTML Structure

The component generates a specific DOM structure with both IDS and legacy BC classes.

**Icon-Only Button:**
```twig
<twig:ibexa:button type="tertiary" icon="edit" icon_size="small-medium" />
```

Renders as:
```html
<button class="ids-btn ids-btn--tertiary ids-btn--icon-only btn ibexa-btn ibexa-btn--ghost">
    <div class="ids-btn__icon">
        <svg class="ibexa-icon ibexa-icon--small-medium ibexa-icon--edit">
            <use xlink:href="{{ ibexa_icon_path('edit') }}"></use>
        </svg>
    </div>
</button>
```

**Button with Text and Icon:**
```twig
<twig:ibexa:button type="primary" icon="trash">Delete</twig:ibexa:button>
```

Renders as:
```html
<button class="ids-btn ids-btn--primary btn ibexa-btn ibexa-btn--primary">
    <div class="ids-btn__icon">
        <svg class="ibexa-icon ibexa-icon--trash">
            <use xlink:href="{{ ibexa_icon_path('trash') }}"></use>
        </svg>
    </div>
    <div class="ids-btn__label">Delete</div>
</button>
```

**Key Points:**
- Icons wrapped in `<div class="ids-btn__icon">`
- Text wrapped in `<div class="ids-btn__label">`
- Both IDS classes (`ids-btn--*`) and legacy BC classes (`ibexa-btn--*`) are rendered

---

## 4. WORKFLOW: Migration Process

### Step 1: Analyze New Cases

When encountering a button pattern not covered in this document:

#### 1.1. Locate the Button Component

The button component is located at:
```
vendor/ibexa/design-system-twig/src/bundle/Resources/views/themes/standard/design_system/components/button.html.twig
```

#### 1.2. Understand the Component Structure

Read the component template to understand:

**Available Variants:**
```twig
variants: {
    type: {
        primary: 'ids-btn--primary',
        secondary: 'ids-btn--secondary',
        tertiary: 'ids-btn--tertiary',
        'secondary-alt': 'ids-btn--secondary-alt',
        'tertiary-alt': 'ids-btn--tertiary-alt',
    },
    size: {
        medium: 'ids-btn--medium',
        small: 'ids-btn--small',
    },
}
```

**How Props Work:**
- `type` - Button variant (maps to CSS classes)
- `size` - Button size (maps to CSS classes)
- `icon` - Icon name (renders `twig:ibexa:icon` component)
- `icon_size` - Size prop passed to icon component
- `html_type` - HTML button type attribute (`button`, `submit`, `reset`)
- `disabled` - Boolean flag for disabled state
- `attributes` - All other HTML attributes passed through

**Component Logic:**
- Icon-only detection: `icon_only = (not has_content) and icon is not empty`
- Disabled class: Adds `ids-btn--disabled` when disabled
- Icon wrapping: `<div class="ids-btn__icon">` → `<twig:ibexa:icon>`
- Label wrapping: `<div class="ids-btn__label">` for text content

#### 1.3. Map Legacy to Component

For each legacy button:

1. **Identify the variant** - Map `ibexa-btn--{variant}` to component `type`
2. **Check size** - Look for `ibexa-btn--small` → `size="small"`
3. **Extract icon** - Pull icon name from SVG `<use>` element
4. **Preserve data attributes** - Copy all `data-*` attributes
5. **Keep custom classes** - Remove `btn`, `ibexa-btn`, variant classes; keep the rest
6. **Check disabled state** - `disabled` attribute → `:disabled="true"`
7. **Verify HTML type** - `type="submit"` → `html_type="submit"`

#### 1.4. Test the Migration

1. Replace the button in the template
2. Render the page and inspect the HTML output
3. Verify all classes, attributes, and structure are correct
4. Test interactive functionality (clicks, disabled state, etc.)

#### 1.5. Document New Patterns

If you discover a new pattern, add it to this document:
- Add to **Variant Mapping** section if new variant
- Add to **Special Cases** if edge case
- Update **Complete Examples** with new example

### Step 2: Adjust Design System Component (If Needed)

In some cases, the button component from `design-system-twig` may need adjustments to support specific use cases.

**Check Recent Commits in `design-system-twig`**

Before making changes, review recent commits to understand component evolution:

```bash
cd vendor/ibexa/design-system-twig
git log -5 --oneline
git show HEAD  # View last commit details
```

**Recent Example:**
```
commit 67ba4d958e3539228df7b3e2099dc40a6ba2d155
Add html_type parameter to button component

Allow setting the HTML button type attribute (button, submit, reset)
via the html_type parameter, defaulting to 'button'.
```

This shows how the component was extended to support `html_type` prop for submit/reset buttons.

**When to Modify the Component:**

If you need to:
- Add new props for BC compatibility (e.g., new variant types)
- Support additional HTML attributes or behaviors
- Handle edge cases (e.g., complex icon scenarios, custom event handlers)
- Add new size variants or icon positions

**Process (Local Changes Only):**
1. Create a **local** branch in `design-system-twig`:
   ```bash
   cd vendor/ibexa/design-system-twig
   git checkout -b feature/button-component-enhancement
   ```
2. Modify the button component template at:
   ```
   src/bundle/Resources/views/themes/standard/design_system/components/button.html.twig
   ```
3. Test the changes with the affected admin-ui templates
4. Ensure backward compatibility - existing usage must not break
5. Create **local** commits with clear messages
6. Update this MIGRATION_RULES.md with the new patterns

**Important:** 
- Always maintain backward compatibility. New props should be optional and not break existing usage.
- ⚠️ **Do NOT push or create PRs automatically** - the user will do this manually when ready

### Step 3: Migration Strategy

**Primary Goal:** Replace ALL buttons in the codebase with the component.

**Process:**
1. Identify all buttons in a template file
2. Replace them one by one using the patterns in this document
3. Run frontend tests after each replacement or batch of replacements
4. Fix any test failures by:
   - Adjusting JS selectors (see section 2: JS HANDOFF)
   - Updating component props
   - Adding missing data attributes
   - Adjusting the component in `design-system-twig` if necessary

**If Tests Cannot Be Fixed:**

After reasonable effort (e.g., 30-60 minutes per button), if frontend tests still fail:

1. **Revert the button replacement** to the legacy markup
2. **Add a TODO comment** above the button:

```twig
{# TODO: Migrate to twig:ibexa:button component
   Reason: [Describe the blocker - e.g., "JS relies on specific DOM structure not supported by component"]
   See: MIGRATION_RULES.md
#}
<button class="btn ibexa-btn ibexa-btn--primary">
    ...
</button>
```

3. **Document the blocker** in a migration tracking issue or this file
4. **Move on** to the next button

**Decision Timeline:**
- Spend reasonable time debugging (30-60 min per button)
- If blocked, mark with TODO and continue
- Don't let one problematic button block the entire migration

### Step 4: Git Workflow - LOCAL CHANGES ONLY

**⚠️ CRITICAL: DO NOT PUSH OR CREATE PRs AUTOMATICALLY**

All changes must remain local until explicitly requested by the user.

**What AI Assistants (Claude, OpenAI Codex, etc.) MUST NOT DO:**
- ❌ DO NOT run `git push` or `git push origin <branch>`
- ❌ DO NOT create pull requests using `gh pr create` or similar commands
- ❌ DO NOT push commits to remote repositories
- ❌ DO NOT force push (`git push --force`)
- ❌ DO NOT automatically merge or rebase with remote branches

**What AI Assistants CAN DO:**
- ✅ Make local file changes
- ✅ Run `git status` to show changes
- ✅ Run `git diff` to show diffs
- ✅ Create local commits with `git add` and `git commit`
- ✅ Create local branches with `git checkout -b <branch>`
- ✅ Run tests locally
- ✅ Show commit history with `git log`

**User Control:**
The user will manually decide when to:
1. Review all changes with `git diff` or `git status`
2. Push changes to remote: `git push origin <branch>`
3. Create pull requests via GitHub UI or `gh` CLI
4. Request code review
5. Merge changes

**Example Workflow:**
```bash
# AI Assistant creates local changes and commits
git add .
git commit -m "Migrate buttons in draft_list.html.twig to twig:ibexa:button component"

# AI Assistant shows status
git status

# USER manually pushes when ready
git push origin feature/button-migration

# USER manually creates PR when ready
gh pr create --title "Migrate buttons to design system component"
```

---

## Migration Checklist

- [ ] Replace `<button>` with `<twig:ibexa:button>`
- [ ] Map `ibexa-btn--{variant}` → `type="{mapped_variant}"`
- [ ] Extract icon from `<svg>` → `icon="{name}"` prop
- [ ] Extract icon size from `ibexa-icon--{size}` → `icon_size="{size}"`
- [ ] Remove `btn`, `ibexa-btn`, variant classes from `class` attribute
- [ ] Keep custom/functional classes in `class` attribute
- [ ] Preserve all `data-*`, `id`, `title` etc attributes
- [ ] Change `type="submit"` → `html_type="submit"`
- [ ] Change `disabled` → `:disabled="true"`
- [ ] Move text from `<span class="ibexa-btn__label">` to component body
- [ ] Self-closing tag for icon-only buttons: `<twig:ibexa:button ... />`
- [ ] Update JavaScript to toggle `ids-btn--disabled` class alongside `disabled` attribute
- [ ] Run frontend tests after replacement
- [ ] If tests fail after reasonable debugging, revert and mark with TODO

---

## Complete Examples

### Example 1: Delete Button with Modal

**OLD:**
```twig
<button
    id="confirm-{{ form.remove.vars.id }}"
    type="button"
    class="btn ibexa-btn ibexa-btn--ghost"
    disabled
    data-bs-toggle="modal"
    data-bs-target="#{{ modal_data_target }}"
>
    <svg class="ibexa-icon ibexa-icon--small-medium ibexa-icon--trash">
        <use xlink:href="{{ ibexa_icon_path('trash') }}"></use>
    </svg>
    <span class="ibexa-btn__label">
        {{ 'drafts.list.action.remove.confirmation.title'|trans|desc('Delete') }}
    </span>
</button>
```

**NEW:**
```twig
<twig:ibexa:button
    type="tertiary"
    icon="trash"
    :disabled="true"
    id="confirm-{{ form.remove.vars.id }}"
    data-bs-toggle="modal"
    data-bs-target="#{{ modal_data_target }}"
>
    {{ 'drafts.list.action.remove.confirmation.title'|trans|desc('Delete') }}
</twig:ibexa:button>
```

### Example 2: Icon-Only Edit Button

**OLD:**
```twig
<button
    class="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text mx-2 ibexa-btn--content-edit"
    title="{{ title }}"
    type="button"
    data-content-id="{{ contentId }}"
    data-language-code="{{ language.languageCode }}"
>
    <svg class="ibexa-icon ibexa-icon--small-medium ibexa-icon--edit">
        <use xlink:href="{{ ibexa_icon_path('edit') }}"></use>
    </svg>
</button>
```

**NEW:**
```twig
<twig:ibexa:button
    type="tertiary"
    icon="edit"
    icon_size="small-medium"
    class="mx-2 ibexa-btn--content-edit"
    title="{{ title }}"
    data-content-id="{{ contentId }}"
    data-language-code="{{ language.languageCode }}"
/>
```

### Example 3: Small Secondary Submit Button

**OLD:**
```twig
<button type="submit" class="btn ibexa-btn ibexa-btn--secondary ibexa-btn--small ibexa-btn--apply" disabled>
    {{ 'search.apply'|trans|desc('Apply') }}
</button>
```

**NEW:**
```twig
<twig:ibexa:button
    type="secondary"
    size="small"
    html_type="submit"
    class="ibexa-btn--apply"
    :disabled="true"
>
    {{ 'search.apply'|trans|desc('Apply') }}
</twig:ibexa:button>
```

### Example 4: Primary Submit with Custom Classes

**OLD:**
```twig
<button type="submit" class="btn ibexa-extra-actions__confirm-btn ibexa-btn ibexa-btn--primary ibexa-btn--content-edit" disabled>
    {{ 'edit_translation.languages.edit'|trans|desc('Edit') }}
</button>
```

**NEW:**
```twig
<twig:ibexa:button
    type="primary"
    html_type="submit"
    class="ibexa-extra-actions__confirm-btn ibexa-btn--content-edit"
    :disabled="true"
>
    {{ 'edit_translation.languages.edit'|trans|desc('Edit') }}
</twig:ibexa:button>
```
