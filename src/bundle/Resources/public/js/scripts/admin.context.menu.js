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

    const clickRelatedBtn = (relatedBtnId) => {
        const button = doc.getElementById(relatedBtnId);

        button.click();
    };

    menuButtons.forEach((menuButton) => {
        const isSplitBtn = menuButton.classList.contains('ibexa-split-btn');

        if (isSplitBtn) {
            const mainBtn = menuButton.querySelector('.ibexa-split-btn__main-btn');
            const splitBtn = menuButton.querySelector('.ibexa-split-btn__toggle-btn');
            const relatedMainBtnId = mainBtn.id;
            const itemLabel = mainBtn.querySelector('.ibexa-btn__label').textContent;

            const item = multilevelPopupMenu.generateItem({
                label: itemLabel,
                branchElement: topBranch,
                groupId: 'default',
                onClick: () => clickRelatedBtn(relatedMainBtnId),
            });
            const subbranch = multilevelPopupMenu.generateBranch({
                triggerElement: item,
                placement: 'left-start',
                fallbackPlacements: ['left-end', 'right-start', 'right-end'],
            });

            const subitemsBtns = splitBtn.branchElement.querySelectorAll('.ibexa-popup-menu__item-content');

            subitemsBtns.forEach((subitemBtn) => {
                const subitemLabel = subitemBtn.querySelector('.ibexa-btn__label').textContent;
                const relatedSubitemBtnId = subitemBtn.id;

                multilevelPopupMenu.generateItem({
                    label: subitemLabel,
                    branchElement: subbranch,
                    groupId: 'default',
                    onClick: () => clickRelatedBtn(relatedSubitemBtnId),
                });
            });
        } else {
            const relatedBtnId = menuButton.id;
            const label = menuButton.querySelector('.ibexa-btn__label').textContent;

            multilevelPopupMenu.generateItem({
                label,
                branchElement: topBranch,
                groupId: 'default',
                onClick: () => clickRelatedBtn(relatedBtnId),
            });
        }
    });

    adaptiveItems.init();
    adapatItemsContainer.classList.remove('ibexa-context-menu--before-adaptive-items-init');
})(window, window.document, window.ibexa);
