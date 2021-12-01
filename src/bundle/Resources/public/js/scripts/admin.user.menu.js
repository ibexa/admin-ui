(function(global, doc) {
    const userMenuContainer = doc.querySelector('.ibexa-main-header__user-menu-column');

    if (!userMenuContainer) {
        return;
    }

    const togglerElement = userMenuContainer.querySelector('.ibexa-header-user-menu__toggler');
    const popupMenuElement = userMenuContainer.querySelector('.ibexa-popup-menu');
    const popupMenu = new eZ.core.PopupMenu({
        triggerElement: togglerElement,
        popupMenuElement,
    });
})(window, window.document);
