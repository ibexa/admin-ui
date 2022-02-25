(function (global, doc, ibexa) {
    const userMenuContainer = doc.querySelector('.ibexa-main-header__user-menu-column');

    if (!userMenuContainer) {
        return;
    }

    const togglerElement = userMenuContainer.querySelector('.ibexa-header-user-menu__toggler');
    const popupMenuElement = userMenuContainer.querySelector('.ibexa-popup-menu');
    new ibexa.core.PopupMenu({
        triggerElement: togglerElement,
        popupMenuElement,
    });
})(window, window.document, window.ibexa);
