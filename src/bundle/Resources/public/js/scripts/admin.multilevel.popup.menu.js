(function (global, doc, ibexa) {
    const initMutlilevelPopupMenus = (container) => {
        const multilevelPopupMenusContainers = container.querySelectorAll(
            '.ibexa-multilevel-popup-menu:not(.ibexa-multilevel-popup-menu--custom-init)',
        );

        multilevelPopupMenusContainers.forEach((multilevelPopupMenusContainer) => {
            const multilevelPopupMenu = new ibexa.core.MultilevelPopupMenu({
                container: multilevelPopupMenusContainer,
                triggerElement: doc.querySelector(multilevelPopupMenusContainer.dataset.triggerElementSelector),
                initialBranchPlacement: multilevelPopupMenusContainer.dataset.initialBranchPlacement,
            });

            multilevelPopupMenu.init();
        });
    };

    initMutlilevelPopupMenus(doc);

    doc.body.addEventListener(
        'ibexa-multilevel-popup-menu:init',
        (event) => {
            const { container } = event.detail;

            initMutlilevelPopupMenus(container);
        },
        false,
    );
})(window, window.document, window.ibexa);
