# Back Office UI spec template

Copy to `docs/ui-specs/<slug>.spec.md` in the **owning package** (the repo whose code
changes most). Keep every section; explicit "none" counts. This is the Gate-1 artifact.

---

```markdown
# <Feature/page name> — BO UI spec

- **Source:** <Figma frame URL + node-id | screenshot filename(s)> (primary saved as `_reference.png` in the scratchpad)
- **Target:** <admin URL(s) where this appears, e.g. /admin/dashboard>
- **Ticket:** IBX-<nnnnn>

## 1. Placement & ownership (from bo-locate)

- Owning package(s): <vendor/ibexa/…> — new files vs modified files split per package
- Affected today: templates <paths>, controllers/routes <names>, JS/React <entries/modules>, SCSS <partials>
- New page/route needed? **no** | yes — <route, controller, menu/tab/zone registration plan>

## 2. Reuse plan

> Check before building anything new, in this order:

- IDS components to use: <`<twig:ibexa:button>`, `<twig:ibexa:dropdown_single:input>`, …>
  (API check: the PHP class in design-system-twig `src/lib/Twig/Components/`)
- Existing admin partials/components: <from `views/themes/admin/ui/component/`, or "none fits because …">
- Existing SCSS/JS to extend rather than duplicate: <…>
- Anything the design shows that is genuinely NEW markup: <…>

## 3. Markup & class plan

> App-level classes are `ibexa-*` BEM (never hand-written `ids-*`). Outline the DOM with
> classes, marking which subtrees come from DS components verbatim.

```
.ibexa-<block>
├── <twig:ibexa:…>            (DS component — its own markup)
└── .ibexa-<block>__<element>
```

## 4. Behavior

- Interactions: <…>
- Implementation: none | vanilla JS (`public/js/scripts/admin.<…>.js`, which Encore entry) |
  React (`ui-dev/src/modules/<…>`, which entry) — follow what the surrounding page already uses
- Backend: form types / controller actions / REST needed: <…>

## 5. Styling

- SCSS partial(s) + which entry pulls them in (e.g. new `_<name>.scss` registered in `ibexa.scss`)
- Follow neighboring partials for variables/mixins; no raw hex where a variable exists

## 6. Translations

- All user-facing strings via `|trans|desc('…')` with the template's `trans_default_domain`;
  list new keys. No hardcoded English in templates/JS.

## 7. Accessibility

- Roles/ARIA for new interactive markup; keyboard path; focus handling for popups/modals

## 8. Verification plan (drives Gate 2)

- URL(s) + states to screenshot (empty/filled/error/hover), viewport(s)
- What "matches the design" means here (structural rubric, not pixel identity)
- Console must be error-free on the affected pages

## 9. Open questions (Gate 1 = answer these)

1. <…>
```
