---
name: bo-verify-live
description: Drive the running Ibexa Back Office with the Playwright MCP browser — login, navigate, screenshot, compare against a design, check console errors — plus the asset rebuild loop. Use to verify admin UI changes live, or as phase 4 of bo-new-ui.
---

# Live Back Office verification

Target app: `IBEXA_BO_URL` env (default `https://localhost:8060`); credentials
`IBEXA_BO_USER`/`IBEXA_BO_PASSWORD` (default dev login `admin`/`publish`). All set/overridden
in `.claude/settings.local.json` `env`.

## One-time setup (per developer)

- Playwright MCP with self-signed-cert tolerance:
  `claude mcp add --scope user playwright -- npx @playwright/mcp@latest --ignore-https-errors`
  (without the flag, navigation dies with `ERR_CERT_AUTHORITY_INVALID`; new/changed
  registration takes effect in the NEXT session). For the human's own browser, the clean
  fix is `symfony server:ca:install` (+ browser restart).
- Check the DXP responds before starting: `curl -sk -o /dev/null -w "%{http_code}" <IBEXA_BO_URL>/admin/login` → 200.

## Login (verified recipe)

1. `browser_navigate` → `<IBEXA_BO_URL>/admin/login`
2. **Type credentials with keystrokes** (`browser_type` with `slowly: true`, or press keys) into
   `#username` / `#password`. A plain value-set (`fill`) leaves the Sign-in button
   **disabled** — the login form is React and only enables it on real input events.
3. Click the `Sign in` button — exact class `ibexa-login__btn--sign-in` (beware: this page
   also has `ids-clear-btn` icon buttons inside the inputs).
4. Success lands on `/admin/dashboard`. The session cookie persists in the MCP browser for
   the rest of the session — log in once.

## Menu navigation (verified recipe)

- Main-menu items are `a.ibexa-main-menu__item-action`. Direct links (Dashboard, Trash,
  Bookmarks…) navigate; category items (Content, Admin, Commerce…) have `href="#…"` and
  expand a second-level panel on click.
- Pattern: click the category, wait ~1s, then click the submenu link **scoped to visible
  elements** — every item also exists hidden in a popup-menu duplicate, so unscoped
  text-matching hits a hidden node and times out. Working example:
  click `Content` → click visible `Content structure` → `/admin/view/content/52/full/1/2`.
- Often simpler: read the submenu link's `href` and navigate to it directly — menu clicks
  are only worth exercising when the menu itself is what you're verifying.

## Verify loop

1. Rebuild if needed: Twig-only edit → just reload; SCSS/JS → `yarn ibexa:dev` in the DXP
   root (or keep `yarn ibexa:watch` running in background); new files/config/template paths →
   `php bin/console cache:clear` (DXP root) as well.
2. `browser_navigate` to the affected URL; use `browser_snapshot` for structure/roles and
   `browser_take_screenshot` (save into the session scratchpad, never into a repo) for
   visual comparison against `_reference.png`.
3. Compare structurally (spacing, hierarchy, components used, states) — not pixel-perfect;
   design exports and a live app with real data never match exactly. Exercise the states the
   spec lists (hover/click/fill via the browser tools).
4. `browser_console_messages` — the affected pages must be free of NEW console errors
   (note pre-existing ones separately).
5. Iterate: fix → rebuild → reload. **Cap: 3 visual iterations**, then show the user the
   side-by-side and let them call it.

## Gotchas

- The dev **Symfony toolbar** sits at the bottom of every page — ignore it in comparisons
  (or screenshot specific elements instead of the full page).
- Wrong-looking page after a successful build: hard-reload (cache), then check you rebuilt
  the right Encore entry (`bo-locate` technique 5 maps element → entry).
- Blank/legacy-looking DS components: the `ids-assets` CSS bridge may be stale — check
  whether the change touched design-system SCSS (that's a different pipeline and build).
- Login redirects you back to `/admin/login` with no error: expired CSRF from a stale tab —
  re-navigate to the login URL fresh.
