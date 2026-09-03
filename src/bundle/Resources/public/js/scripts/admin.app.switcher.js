(function (global, doc, ibexa) {
    const APP_SWITCHER_OPEN_COOKIE_NAME = 'ibexa-aui_app-switcher-is-open';
    const CLASS_PANEL_HIDDEN = 'ibexa-app-switcher-panel--hidden';
    const CLASS_TOGGLER_COLUMN_OPEN = 'ibexa-main-header__app-switcher-column--open';
    const SELECTOR_TOGGLER = '.ibexa-app-switcher-toggler';
    const SELECTOR_TOGGLER_COLUMN = '.ibexa-main-header__app-switcher-column';
    const toggler = doc.querySelector(SELECTOR_TOGGLER);

    if (!toggler) {
        return;
    }

    const panel = doc.getElementById(toggler.getAttribute('aria-controls'));
    const togglerColumn = toggler.closest(SELECTOR_TOGGLER_COLUMN);

    if (!panel || !togglerColumn) {
        return;
    }

    const isOpen = () => !panel.classList.contains(CLASS_PANEL_HIDDEN);
    const setOpen = (shouldBeOpen) => {
        panel.classList.toggle(CLASS_PANEL_HIDDEN, !shouldBeOpen);
        togglerColumn.classList.toggle(CLASS_TOGGLER_COLUMN_OPEN, shouldBeOpen);
        toggler.setAttribute('aria-expanded', shouldBeOpen ? 'true' : 'false');
        ibexa.helpers.cookies.setBackOfficeCookie(APP_SWITCHER_OPEN_COOKIE_NAME, shouldBeOpen);
    };
    const close = ({ shouldRestoreFocus = false } = {}) => {
        setOpen(false);

        if (shouldRestoreFocus) {
            toggler.focus();
        }
    };

    toggler.addEventListener(
        'click',
        () => {
            setOpen(!isOpen());
        },
        false,
    );

    doc.addEventListener(
        'keydown',
        (event) => {
            if (event.key === 'Escape' && isOpen()) {
                close({ shouldRestoreFocus: true });
            }
        },
        false,
    );
})(window, window.document, window.ibexa);
