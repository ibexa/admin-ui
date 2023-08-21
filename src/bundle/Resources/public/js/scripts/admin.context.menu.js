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
                const shouldBeVisible = hiddenButtonsIds.includes(branchItem.dataset.relatedBtnId);

                multilevelPopupMenu.toggleItemVisibility(branchItem, shouldBeVisible);
            });
        },
    });
    const clickRelatedBtn = (relatedBtnId) => {
        const button = doc.getElementById(relatedBtnId);

        button.click();
    };
    const addRelatedBtnIdToMenuItem = (itemElement, relatedBtnId) => (itemElement.dataset.relatedBtnId = relatedBtnId);
    const multilevelPopupMenu = new ibexa.core.MultilevelPopupMenu({
        container: popupMenuElement,
        triggerElement: showPopupButton,
    });
    const topBranchItems = menuButtons.map((menuButton) => {
        const isSplitBtn = menuButton.classList.contains('ibexa-split-btn');

        if (isSplitBtn) {
            const mainBtn = menuButton.querySelector('.ibexa-split-btn__main-btn');
            const splitBtn = menuButton.querySelector('.ibexa-split-btn__toggle-btn');
            const relatedMainBtnId = mainBtn.id;
            const mainBtnLabel = mainBtn.querySelector('.ibexa-btn__label').textContent;
            const {
                alternativeMainBtnLabel: mainBtnAlternativeLabel,
                alternativeMainBtnSublabel: mainBtnAlternativeSublabel,
                alternativeToggleLabel,
            } = menuButton.dataset;
            const subitemsBtns = [...splitBtn.branchElement.querySelectorAll('.ibexa-popup-menu__item-content')];

            const subitems = subitemsBtns.map((subitemBtn) => {
                const subitemLabel = subitemBtn.querySelector('.ibexa-btn__label').textContent;
                const relatedSubitemBtnId = subitemBtn.id;

                return {
                    label: subitemLabel,
                    onClick: () => clickRelatedBtn(relatedSubitemBtnId),
                    processAfterCreated: (itemElement) => addRelatedBtnIdToMenuItem(itemElement, relatedSubitemBtnId),
                };
            });

            return {
                label: alternativeToggleLabel ?? mainBtnLabel,
                onClick: () => clickRelatedBtn(relatedMainBtnId),
                processAfterCreated: (itemElement) => addRelatedBtnIdToMenuItem(itemElement, relatedMainBtnId),
                branch: {
                    groups: [
                        {
                            id: 'main',
                            items: [
                                {
                                    label: mainBtnAlternativeLabel ?? mainBtnLabel,
                                    sublabel: mainBtnAlternativeSublabel,
                                },
                            ],
                        },
                        {
                            id: 'subitems',
                            items: subitems,
                        },
                    ],
                },
            };
        }

        const relatedBtnId = menuButton.id;
        const label = menuButton.querySelector('.ibexa-btn__label').textContent;

        return {
            label,
            groupId: 'default',
            onClick: () => clickRelatedBtn(relatedBtnId),
            processAfterCreated: (itemElement) => addRelatedBtnIdToMenuItem(itemElement, relatedBtnId),
        };
    });
    const menuTree = {
        triggerElement: showPopupButton,
        placement: 'bottom-end',
        fallbackPlacements: ['bottom-start', 'top-end', 'top-start'],
        groups: [
            {
                id: 'default',
                items: topBranchItems,
            },
        ],
    };

    multilevelPopupMenu.init();
    const topBranch = multilevelPopupMenu.generateMenu(menuTree);

    adaptiveItems.init();
    adapatItemsContainer.classList.remove('ibexa-context-menu--before-adaptive-items-init');
})(window, window.document, window.ibexa);
