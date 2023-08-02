(function (global, doc, ibexa) {
    const adapatItemsContainer = doc.querySelector('.ibexa-context-menu');

    if (!adapatItemsContainer) {
        return;
    }

    const menuButtons = [
        ...adapatItemsContainer.querySelectorAll(
            '.ibexa-context-menu__item > .ibexa-btn:not(.ibexa-btn--more), .ibexa-context-menu__item > .ibexa-split-btn',
        ),
    ];
    const popupMenuElement = adapatItemsContainer.querySelector('.ibexa-context-menu__item--more .ibexa-multilevel-popup-menu');
    const showPopupButton = adapatItemsContainer.querySelector('.ibexa-btn--more');

    if (!showPopupButton) {
        return;
    }

    const adaptiveItems = new ibexa.core.AdaptiveItems({
        itemHiddenClass: 'ibexa-context-menu__item--hidden',
        container: adapatItemsContainer,
        getActiveItem: () => {
            return adapatItemsContainer.querySelector('.ibexa-context-menu__item');
        },
        onAdapted: (visibleItems, hiddenItems) => {
            const hiddenButtonsIds = [...hiddenItems].map((item) => item.querySelector('.ibexa-btn').id);
            const topBranchItems = multilevelPopupMenu.getBranchItems(topBranch);

            topBranchItems.forEach((branchItem) => {
                const shouldBeVisible = hiddenButtonsIds.includes(branchItem.dataset.relatedButtonId);

                multilevelPopupMenu.toggleItemVisibility(branchItem, shouldBeVisible);
            });
        },
    });
    const multilevelPopupMenu = new ibexa.core.MultilevelPopupMenu({
        container: popupMenuElement,
        triggerElement: showPopupButton,
    });

    const topBranch = multilevelPopupMenu.generateBranch({
        triggerElement: showPopupButton,
        placement: 'bottom-end',
        fallbackPlacements: ['bottom-start', 'top-end', 'top-start'],
    });

    menuButtons.forEach((menuButton) => {
        const isSplitBtn = menuButton.classList.contains('ibexa-split-btn');

        if (isSplitBtn) {
            const mainBtn = menuButton.querySelector('.ibexa-split-btn__main-btn');
            const relatedMainBtnId = mainBtn.id;
            const itemLabel = mainBtn.querySelector('.ibexa-btn__label').textContent;

            const item = multilevelPopupMenu.generateItem(
                {
                    label: itemLabel,
                    branchElement: topBranch,
                },
                (newBranchElement) => {
                    newBranchElement.dataset.relatedButtonId = relatedMainBtnId;
                },
            );
            const subbranch = multilevelPopupMenu.generateBranch({
                triggerElement: item,
                placement: 'left-start',
                fallbackPlacements: ['left-end', 'right-start', 'right-end'],
            });

            const subitemsBtns = menuButton.querySelectorAll(
                '.ibexa-multilevel-popup-menu__branch .ibexa-multilevel-popup-menu__item-content',
            );

            subitemsBtns.forEach((subitemBtn) => {
                const subitemLabel = subitemBtn.querySelector('.ibexa-btn__label').textContent;

                multilevelPopupMenu.generateItem({
                    label: subitemLabel,
                    branchElement: subbranch,
                });
            });
        } else {
            const relatedButtonId = menuButton.id;
            const label = menuButton.querySelector('.ibexa-btn__label').textContent;

            multilevelPopupMenu.generateItem(
                {
                    label,
                    branchElement: topBranch,
                },
                (newItemElement) => {
                    newItemElement.dataset.relatedButtonId = relatedButtonId;
                },
            );
        }
    });
    // const popupMenu = new ibexa.core.PopupMenu({
    //     popupMenuElement,
    //     triggerElement: showPopupButton,
    //     onItemClick: (event) => {
    //         const { relatedButtonId } = event.currentTarget.dataset;
    //         const button = doc.getElementById(relatedButtonId);

    //         button.click();
    //     },
    // });
    // const popupItemsToGenerate = [...menuButtons].map((button) => {
    //     const relatedButtonId = button.id;
    //     const label = button.querySelector('.ibexa-btn__label').textContent;

    //     return {
    //         label,
    //         relatedButtonId,
    //         disabled: button.disabled,
    //     };
    // });

    // popupMenu.generateItems(popupItemsToGenerate, (itemElement, item) => {
    //     const itemContentElement = itemElement.querySelector('.ibexa-popup-menu__item-content');

    //     itemElement.dataset.relatedButtonId = item.relatedButtonId;

    //     if (item.disabled) {
    //         itemContentElement.classList.add('ibexa-popup-menu__item-content--disabled');
    //     }
    // });

    adaptiveItems.init();
    adapatItemsContainer.classList.remove('ibexa-context-menu--before-adaptive-items-init');
})(window, window.document, window.ibexa);
