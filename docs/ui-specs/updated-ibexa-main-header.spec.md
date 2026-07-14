# Updated `ibexa-main-header` — BO UI spec

- **Source:** Figma "[i1] Login / side-bar / top-bar unification"
  - Header (redesign target): node `13802-19367` → scratchpad `design-header_reference.png`
  - Menu (correctness check only): node `14599-70487` → scratchpad `design-menu_reference.png`
  - Page: <https://www.figma.com/design/3s5Epwbsm6GcLes2aqo9Hp/-i1--Login--side-bar--top-bar-unification?node-id=13666-73988>
  - Live baseline captured this session: scratchpad `baseline-dashboard.png`
- **Target:** `https://localhost:8060/admin/dashboard` (the header renders on every BO page)
- **Ticket:** IBX-8888888

> ✅ **Gate 1 resolved (2026-07-11).** The header on `new-menu` already matches the Figma
> except for **one** thing: the **header is too tall**. Confirmed scope:
> - **Reduce header height 72px → 64px** (the only implementation change). — admin-ui only.
> - App-switcher waffle: **leave it.** Avatar caret: **leave it.**
> - site-context selector: **leave as-is.**
> - Menu (node 14599-70487): **report only** — Dashboard-under-Content discrepancy is a
>   flagged finding, no `MainMenuBuilder` change.
>
> Net: the only package touched is **admin-ui**; the only file changed is
> `_main-header.scss` (vertical padding reduced to yield a 64px header).

---

## 1. Placement & ownership (from bo-locate)

**Owning package:** `ibexa/admin-ui` (branch `new-menu`) renders the header shell.

The header (`<header class="ibexa-main-header">`, `layout.html.twig:123-150`) is composed of
three columns, and its right cluster is assembled from **three packages**:

| Element | Rendered by | Package / branch |
|---|---|---|
| `<header>` shell, brand, search wrapper, user-menu wrapper | `ui/layout.html.twig` `{% block header_row %}` | admin-ui / `new-menu` |
| Brand logo + `user_mode_badge` ("Focus mode") | `ui/user_mode_badge.html.twig` | admin-ui / `new-menu` |
| Global search (centered) | `admin-ui-global-search` group → `GlobalSearchTwigComponent` | admin-ui / `new-menu` |
| **"Site: All context" selector** | injected into `admin-ui-user-menu` group @ **priority 100** | **site-context / `ds-development`** |
| Bell + notice badge + avatar + caret (`.ibexa-header-user-menu`) | `ui/menu/user.html.twig` | admin-ui / `new-menu` |
| **App-switcher "waffle" icon** (middle slot between bell & avatar) | `admin-ui-header-user-menu-middle` group | **app-switcher / `6.0`** |

- **Affected today (candidate changes only):**
  - Templates: `src/bundle/Resources/views/themes/admin/ui/menu/user.html.twig` (caret)
  - SCSS: `src/bundle/Resources/public/scss/_header-user-menu.scss`, `_main-header.scss`
  - app-switcher: `Resources/config/services/components.yaml` or its component (waffle removal)
- **New page/route needed?** **no.**

## 2. Reuse plan

Nothing new to build. The design maps onto markup/SCSS that already exists on `new-menu`:

- **Vertical divider** (design shows a rule between the site selector and the bell/avatar
  cluster) → **already implemented** as `.ibexa-header-user-menu { border-left: 1px solid
  $ibexa-color-light; padding-left: 16px }` (`_header-user-menu.scss:9-10`).
- **Numeric notification badge** (design shows a red "1") → **already implemented**:
  `.ibexa-header-user-menu__notice-dot::after { content: attr(data-count) }`
  (`_header-user-menu.scss:105-110`); hidden via `--no-notice` when count is 0. The "1" in
  the Figma is just a data state (one unread notification), not new markup.
- **"Focus mode" pill** next to the brand → already the `user_mode_badge` component; only
  visible when focus mode is on (state, not a delta).
- IDS components: none required — this is a shell/styling change, no `<twig:ibexa:*>` added.

## 3. Markup & class plan

Current right-cluster DOM (matches the design except the two ⚠️ items):

```
.ibexa-main-header
├── .ibexa-main-header__brand-column        (logo + user_mode_badge "Focus mode")
├── .ibexa-main-header__search-column       (centered global search — magnifier on right)
└── .ibexa-main-header__user-menu-column
    ├── [site-context] "Site: All context ⌄"        (site-context pkg, priority 100)
    └── .ibexa-header-user-menu                       (border-left = the divider)
        ├── __notifications-toggler → bell + __notice-dot[data-count]   ✅ matches
        ├── #qntm-app-switcher  (waffle)                 ⚠️ NOT in design — remove?
        └── __toggler → __thumbnail-wrapper (avatar) + caret-down icon  ⚠️ caret NOT in design — remove?
```

**Only two proposed markup deltas** (pending Gate 1):
1. Remove the app-switcher waffle from the header middle slot (app-switcher package).
2. Remove the `caret-down` `<svg>` from `.ibexa-header-user-menu__toggler` in
   `user.html.twig:24-26` (and the `.ibexa-icon` margin/rotate rules in
   `_header-user-menu.scss:47-59` that only serve the caret).

## 4. Behavior

- No behavior change intended. Removing the caret is cosmetic — the avatar `__toggler`
  button still opens the popup menu on click. Removing the waffle removes the app-switcher
  entry point entirely (behavioral — hence a Gate-1 question, not an assumption).
- Implementation: none / template + SCSS only. No JS or backend changes.

## 5. Styling

- `_header-user-menu.scss` (already registered via `ibexa.scss`) — remove caret-specific
  `.ibexa-icon` rules if the caret is dropped. No new partials.
- `_main-header.scss` — reviewed; padding/height/background already match the design
  (72px tall, `$ibexa-color-dark`, `border-bottom $ibexa-color-dark-500`). No change unless
  a pixel diff surfaces at Gate 2.
- No raw hex; existing `$ibexa-color-*` + `calculateRem()` conventions already followed.

## 6. Translations

- No new strings. "Site: All context" already keyed in
  `site-context/…/translations/ibexa_site_context.en.xliff` ("All context").

## 7. Accessibility

- Dropping the caret `<svg>` (decorative, no label) has no a11y impact; the avatar button
  keeps its accessible action. If the waffle is removed, confirm no keyboard focus target is
  orphaned (it's a self-contained React widget, so removing its component removes it cleanly).

## 8. Verification plan (drives Gate 2)

- **Header:** screenshot `/admin/dashboard` at 1440×900, logged in. Compare right cluster
  against `design-header_reference.png`: divider present, bell+badge, avatar — and
  waffle/caret state per the approved scope. Also toggle Focus mode to confirm the badge.
- **Menu (check only):** expand the left sidebar and screenshot; compare top-level order and
  the bottom-anchored group against `design-menu_reference.png`.
- "Matches" = structural rubric (element set, order, divider, badge), not pixel identity.
- Console must be error-free on `/admin/dashboard`.

## 9. Open questions (Gate 1 = answer these)

1. **Scope reality check.** The header already matches the Figma on `new-menu`. Do you
   want me to (a) only apply the two deltas below, (b) also hunt for sub-pixel diffs
   (spacing/typography) via a close Gate-2 overlay, or (c) something specific in the design
   I may be reading wrong from the screenshot? If you can point at *what* looks off live,
   I'll target it.
2. **App-switcher waffle** — the Figma header has **no** waffle icon; live does (between
   bell and avatar, from the `app-switcher` package on branch `6.0`). Remove it from the
   header? That's a cross-package + behavioral change → needs your call. (If yes, I branch
   `IBX-8888888-updated-ibexa-main-header` off `6.0` in `app-switcher`.)
3. **Avatar caret** — the Figma shows a bare avatar; live shows a `caret-down` next to it.
   Remove the caret? (admin-ui only, cosmetic.)
4. **site-context selector** — it's on `ds-development`, not `new-menu`. If any restyle of
   "Site: All context" is needed, I'd branch off `ds-development` there. Any change wanted,
   or leave as-is?

## Appendix — Menu correctness check (Figma node 14599-70487)

You asked me to check whether the menu "was designed correctly." Comparing the Figma menu
frame to the current `MainMenuBuilder` (`src/lib/Menu/MainMenuBuilder.php`) on `new-menu`:

- **Design top-level order:** Dashboard (active) · Content · Site management · Product
  management · Commerce · Customers · Engage. **Bottom-anchored:** Administration ·
  Favourites · Trash.
- **✅ Correct:** the bottom group (Administration / Favourites=`bookmarks` / Trash) is
  built with `extras: { bottom_item: true }` — matches the design's anchored bottom section.
  Expandable top items (chevrons) and the "Collapse" toggle are present in the sidebar.
- **⚠️ Discrepancy — Dashboard placement:** in the code, **Dashboard is a child of
  `Content`** (`MainMenuBuilder.php:178`, alongside `Drafts`), so it renders *inside* the
  Content submenu — but the Figma places **Dashboard as the first top-level item** (and the
  active one). Either the design is wrong, or the menu build needs Dashboard promoted to
  top level. **This is a design-vs-implementation mismatch to resolve** — flagging, not
  fixing (menu was "check only" per your ask). Items Site management / Product management /
  Commerce / Customers / Engage are contributed by other packages via `ConfigureMenuEvent`;
  I'll confirm their presence/order live during Gate-2 if you want the menu in scope.
