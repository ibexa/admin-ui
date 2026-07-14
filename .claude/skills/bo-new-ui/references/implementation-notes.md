# Implementation notes

Read this repo's `CLAUDE.md` first (architecture, extension points, build). These notes are
the deltas that matter when implementing a design.

## DS components first

- Twig: `<twig:ibexa:button>`, `<twig:ibexa:tag>`, `<twig:ibexa:dropdown_single:input>`, …
  **Never guess props** — read the component's PHP class in
  `<IDS_DXP_ROOT>/vendor/ibexa/design-system-twig/src/lib/Twig/Components/<Name>.php`
  (public props + `#[PreMount]` OptionsResolver allowed values) and its template for blocks.
- Syntax: literals `prop="value"`, expressions `:prop="expr"`, children between tags.
  Use `class=` (never `className=`); prefer `icon_url` over legacy `iconPath`.
- React (inside `ui-dev` modules): `import { Button, ButtonType } from '@ids-components/components/Button';`
- If the design needs a component that does not exist in the DS: STOP — that's a scope
  question for the user (new DS component = the `ids-new-component` pipeline in the React DS
  repo, a separate effort), not something to hand-roll here.

## App-level conventions

- Classes: `ibexa-*` loose BEM, matching the surrounding template's style. `ids-*` classes
  come only out of DS components.
- Twig: `{% trans_default_domain '<domain>' %}` + `|trans|desc('English text')` for every
  user-facing string; template names/vars snake_case; extend/include via `@ibexadesign`.
- SCSS: new partial under `public/scss/`, registered in the entry the page uses
  (usually `ibexa.scss`); reuse the repo's variables/mixins; no raw hex where a variable
  exists; `calculateRem()` where neighbors use it.
- JS: match the page's generation — don't introduce React into a vanilla-JS page or vice
  versa without flagging it in the spec. New vanilla scripts go in
  `public/js/scripts/admin.<area>.<thing>.js` and must be added to the right Encore entry in
  `Resources/encore/ibexa.js.config.js`.
- New page: route in `Resources/config/routing.yaml`, controller in `src/bundle/Controller/`,
  menu item via `ConfigureMenuEvent` subscriber, template extending
  `@ibexadesign/ui/layout.html.twig`. New zone-injected UI: register in the
  `ibexa/twig-components` Registry (the in-package one is deprecated).

## Cross-package work

Other `vendor/ibexa/*` packages own their UI (product-catalog, order-management, …) — same
conventions, own git repo, coordinated branch names. The spec's §1 decides which repo each
file belongs to; never put package-specific UI into admin-ui just because it's handy.

## After editing

- Twig-only changes: refresh the page (no build). New/renamed template files or config:
  `php bin/console cache:clear` in the DXP root.
- SCSS/JS changes: rebuild from the DXP root — `yarn ibexa:dev` (or keep `yarn ibexa:watch`
  running in the background).
- Then verify live: `bo-verify-live`.
