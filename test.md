Use the bo-new-ui skill. I want to implement this Figma design in the Back Office, but only part of it:

link to a page: https://www.figma.com/design/3s5Epwbsm6GcLes2aqo9Hp/-i1--Login--side-bar--top-bar-unification?node-id=13666-73988
I want to redesign specifically: https://www.figma.com/design/3s5Epwbsm6GcLes2aqo9Hp/-i1--Login--side-bar--top-bar-unification?node-id=13802-19367&t=oOUCRGJMUO9uDpGN-1 (it exists on more screens on the above figma page) which is ibexa-main-header in our project
you can also check whether menu https://www.figma.com/design/3s5Epwbsm6GcLes2aqo9Hp/-i1--Login--side-bar--top-bar-unification?node-id=14599-70487&t=oOUCRGJMUO9uDpGN-1 (it exists on more screens on the above figma page) was designed correctly

- It belongs on this admin page: https://localhost:8060/admin/dashboard
- Ticket: IBX-8888888
- Create feature branches IBX-8888888-updated-ibexa-main-header from new-menu (or ds-development if new-menu not present) in every package you touch.
- Full pipeline: locate the owning code first, then spec and stop for my approval,
  then implement, rebuild assets, and verify live in the running Back Office with
  Playwright — show me the design vs. live screenshots side by side before finishing.