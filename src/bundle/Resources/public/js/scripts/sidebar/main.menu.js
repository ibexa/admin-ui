(function (global, doc, ibexa) {
    const SECOND_LEVEL_COLLAPSED_WIDTH = 48;
    const SECOND_LEVEL_EXPANDED_WIDTH = 220;
    const SECOND_LEVEL_MANUAL_RESIZE_MIN_WIDTH = 80;
    const mainMenuNode = doc.querySelector('.ibexa-main-menu');

    if (!mainMenuNode) {
        return;
    }

    const firstLevelMenuNode = mainMenuNode.querySelector('.ibexa-main-menu__navbar--first-level');
    const secondLevelMenuNode = mainMenuNode.querySelector('.ibexa-main-menu__navbar--second-level');
    const showFistLevelPopupButton = firstLevelMenuNode.querySelector('.ibexa-main-menu__item--more');
    const firstLevelPopupMenu = firstLevelMenuNode.querySelector('.ibexa-main-menu__first-level-popup-menu');
    const adaptiveMenuItemsContainer = firstLevelMenuNode.querySelector('.ibexa-adaptive-items');
    const selectorItem = firstLevelMenuNode.querySelector('.ibexa-adaptive-items__item--selector');
    const adaptiveItemsToPopup = firstLevelMenuNode.querySelectorAll('.ibexa-adaptive-items__item');
    const popupItemsToGenerate = [...adaptiveItemsToPopup].map((item) => {
        const actionItem = item.querySelector('.ibexa-main-menu__item-action');
        const name = item.dataset.itemName;
        const label = item.querySelector('.ibexa-main-menu__item-text-column')?.textContent;
        const isActive = actionItem.classList.contains('active');

        return {
            name,
            label,
            isActive,
        };
    });
    const { resizerWidth } = secondLevelMenuNode.dataset;
    let resizeStartPositionX = 0;
    let secondMenuLevelCurrentWidth = secondLevelMenuNode.getBoundingClientRect().width;
    const collapseSecondLevelMenu = (event) => {
        if (event.target.closest('.ibexa-main-menu__navbar') || event.target.closest('.ibexa-tooltip')) {
            return;
        }

        toggleSecondLevelMenu();

        doc.removeEventListener('mousemove', collapseSecondLevelMenu);
    };
    const showSecondLevelMenu = ({ currentTarget }) => {
        if (!currentTarget.dataset.bsToggle) {
            return;
        }

        firstLevelMenuNode.classList.add('ibexa-main-menu__navbar--collapsed');
        secondLevelMenuNode.classList.remove('ibexa-main-menu__navbar--hidden');

        currentTarget.blur();

        if (secondLevelMenuNode.classList.contains('ibexa-main-menu__navbar--collapsed')) {
            toggleSecondLevelMenu();

            doc.addEventListener('mousemove', collapseSecondLevelMenu, false);
        } else {
            setWidthOfSecondLevelMenu();
        }
    };
    const setWidthOfSecondLevelMenu = () => {
        const secondLevelMenuWidth = ibexa.helpers.cookies.getCookie('ibexa-aui_menu-secondary-width');
        const isSecondLevelMenuHidden = secondLevelMenuNode.classList.contains('ibexa-main-menu__navbar--hidden');

        if (!secondLevelMenuWidth || isSecondLevelMenuHidden) {
            return;
        }

        const secondLevelMenuListWidth = secondLevelMenuWidth - resizerWidth;

        secondLevelMenuNode.style.width = `${secondLevelMenuWidth}px`;
        secondLevelMenuNode.querySelectorAll('.ibexa-main-menu__tab-pane .ibexa-main-menu__items-list').forEach((itemList) => {
            itemList.style.width = `${secondLevelMenuListWidth}px`;
        });
        secondLevelMenuNode.classList.toggle(
            'ibexa-main-menu__navbar--collapsed',
            secondLevelMenuWidth <= SECOND_LEVEL_MANUAL_RESIZE_MIN_WIDTH,
        );

        doc.body.dispatchEvent(new CustomEvent('ibexa-main-menu-resized'));
    };
    const toggleSecondLevelMenu = () => {
        const isSecondLevelMenuCollapsed = secondLevelMenuNode.classList.contains('ibexa-main-menu__navbar--collapsed');
        const newMenuWidth = isSecondLevelMenuCollapsed ? SECOND_LEVEL_EXPANDED_WIDTH : SECOND_LEVEL_COLLAPSED_WIDTH;

        ibexa.helpers.cookies.setBackOfficeCookie('ibexa-aui_menu-secondary-width', newMenuWidth);
        setWidthOfSecondLevelMenu();
    };
    const parsePopup = (button) => {
        const { popupTargetSelector } = button.dataset;
        const popupNode = doc.querySelector(popupTargetSelector);

        if (!popupNode) {
            return;
        }

        new ibexa.core.PopupMenu({
            popupMenuElement: popupNode,
            triggerElement: button,
        });
    };
    const parseMenuTitles = () => {
        ibexa.helpers.tooltips.hideAll();

        firstLevelMenuNode.querySelectorAll('.ibexa-main-menu__item').forEach((item) => {
            const labelNode = item.querySelector('.ibexa-main-menu__item-text-column');

            if (labelNode) {
                const label = labelNode.textContent;

                if (firstLevelMenuNode.classList.contains('ibexa-main-menu__navbar--collapsed')) {
                    item.setAttribute('title', label);
                }

                ibexa.helpers.tooltips.parse(mainMenuNode);
            }
        });
    };
    const addResizeListeners = ({ clientX }) => {
        resizeStartPositionX = clientX;
        secondLevelMenuNode.classList.add('ibexa-main-menu__navbar--resizing');
        secondMenuLevelCurrentWidth = secondLevelMenuNode.getBoundingClientRect().width;

        doc.addEventListener('mousemove', triggerSecondLevelChangeWidth, false);
        doc.addEventListener('mouseup', removeResizeListeners, false);
    };
    const removeResizeListeners = () => {
        secondLevelMenuNode.classList.remove('ibexa-main-menu__navbar--resizing');
        doc.removeEventListener('mousemove', triggerSecondLevelChangeWidth, false);
        doc.removeEventListener('mouseup', removeResizeListeners, false);
    };
    const triggerSecondLevelChangeWidth = ({ clientX }) => {
        const resizeValue = secondMenuLevelCurrentWidth + (clientX - resizeStartPositionX);
        const newMenuWidth = resizeValue > SECOND_LEVEL_MANUAL_RESIZE_MIN_WIDTH ? resizeValue : SECOND_LEVEL_COLLAPSED_WIDTH;

        ibexa.helpers.cookies.setBackOfficeCookie('ibexa-aui_menu-secondary-width', newMenuWidth);
        setWidthOfSecondLevelMenu();
    };

    parseMenuTitles();

    firstLevelMenuNode.querySelectorAll('.ibexa-main-menu__item-action').forEach((button) => {
        button.addEventListener('click', showSecondLevelMenu, false);
    });

    secondLevelMenuNode.querySelector('.ibexa-main-menu__toggler').addEventListener('click', toggleSecondLevelMenu, false);
    secondLevelMenuNode.querySelector('.ibexa-main-menu__resizer').addEventListener('mousedown', addResizeListeners, false);
    secondLevelMenuNode.querySelectorAll('.ibexa-main-menu__tooltip-trigger').forEach(parsePopup);
    secondLevelMenuNode.addEventListener(
        'transitionend',
        (event) => {
            if (event.propertyName === 'width') {
                doc.body.dispatchEvent(new CustomEvent('ibexa-content-resized'));
            }
        },
        false,
    );
    secondLevelMenuNode.addEventListener('ibexa-menu:hide', () => {
        ibexa.helpers.cookies.setBackOfficeCookie('ibexa-aui_menu-secondary-width', SECOND_LEVEL_COLLAPSED_WIDTH);
    });

    if (showFistLevelPopupButton && selectorItem) {
        const adaptiveItems = new ibexa.core.AdaptiveItems({
            itemHiddenClass: 'ibexa-context-menu__item--hidden',
            container: adaptiveMenuItemsContainer,
            isVertical: true,
            selectorItem,
            getActiveItem: () => {},
            onAdapted: (visibleItems, hiddenItems) => {
                const hiddenItemNames = [...hiddenItems].map((item) => item.dataset.itemName);

                popupMenu.toggleItems((popupMenuItem) => !hiddenItemNames.includes(popupMenuItem.dataset.relatedItemName));
                popupMenu.updatePosition();
            },
        });
        const popupMenu = new ibexa.core.PopupMenu({
            popupMenuElement: firstLevelPopupMenu,
            triggerElement: showFistLevelPopupButton,
            onItemClick: ({ currentTarget }) => {
                const { relatedItemName } = currentTarget.dataset;
                const relatedItemAction = doc.querySelector(`[data-item-name="${relatedItemName}"] .ibexa-main-menu__item-action`);

                relatedItemAction.click();
            },
            position: () => {
                const popupLeftOffset = 5;
                const targetTopPosition = selectorItem.offsetTop;
                const targetLeftPosition = selectorItem.offsetLeft + selectorItem.offsetWidth + popupLeftOffset;

                firstLevelPopupMenu.style.top = `${targetTopPosition}px`;
                firstLevelPopupMenu.style.left = `${targetLeftPosition}px`;
            },
        });

        popupMenu.generateItems(popupItemsToGenerate, (itemElement, item) => {
            const itemElementContent = itemElement.querySelector('.ibexa-popup-menu__item-content');

            itemElement.dataset.relatedItemName = item.name;
            itemElementContent.classList.toggle('ibexa-popup-menu__item-content--current', item.isActive);
        });

        popupMenu.updatePosition();
        adaptiveItems.init();
    }
})(window, window.document, window.ibexa);
