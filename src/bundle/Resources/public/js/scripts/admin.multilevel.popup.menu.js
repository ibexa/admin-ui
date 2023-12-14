(function (global, doc, ibexa) {
    const multilevelPopupMenusContainers = doc.querySelectorAll(
        '.ibexa-multilevel-popup-menu:not(.ibexa-multilevel-popup-menu--custom-init)',
    );

    multilevelPopupMenusContainers.forEach((container) => {
        const multilevelPopupMenu = new ibexa.core.MultilevelPopupMenu({
            container,
            triggerElement: doc.querySelector(container.dataset.triggerElementSelector),
            initialBranchPlacement: container.dataset.initialBranchPlacement,
        });

        multilevelPopupMenu.init();
    });
})(window, window.document, window.ibexa);
