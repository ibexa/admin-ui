(function (global, doc, ibexa) {
    const mainMenuNode = doc.querySelector('.ibexa-main-menu');

    if (!mainMenuNode) {
        return;
    }

    const objectInstances = ibexa.helpers.objectInstances;
    const firstLevelMenuNode = mainMenuNode.querySelector('.ibexa-main-menu__navbar--first-level');
    const expandToggleButton = firstLevelMenuNode.querySelector('.ibexa-main-menu__expand-toggler');
    const firstLevelNavItems = [...firstLevelMenuNode.querySelectorAll('.ibexa-main-menu__item[data-item-name]')];
    const parentItemButtons = firstLevelMenuNode.querySelectorAll(
        '.ibexa-main-menu__item-action--popup-trigger, .ibexa-main-menu__item-action--accordion-trigger',
    );
    const popupTriggerButtons = firstLevelMenuNode.querySelectorAll('.ibexa-main-menu__item-action--popup-trigger');
    const popupMenuInstances = new Map();
    const isMenuExpanded = () => !firstLevelMenuNode.classList.contains('ibexa-main-menu__navbar--collapsed');
    const { collapseLabel, expandLabel } = expandToggleButton.dataset;
    const syncExpandToggleButtonState = (isExpanded) => {
        const tooltipInstance = global.bootstrap?.Tooltip.getInstance(expandToggleButton);

        expandToggleButton.classList.toggle('ibexa-main-menu__expand-toggler--collapsed', !isExpanded);
        expandToggleButton.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        expandToggleButton.setAttribute('aria-label', isExpanded ? collapseLabel : expandLabel);

        if (isExpanded) {
            tooltipInstance?.dispose();
            expandToggleButton.removeAttribute('title');
            expandToggleButton.removeAttribute('data-tooltip-placement');
            expandToggleButton.removeAttribute('data-original-title');
            expandToggleButton.removeAttribute('data-bs-original-title');
        } else {
            expandToggleButton.setAttribute('title', expandLabel);
        }
    };
    const parseMenuTitles = () => {
        ibexa.helpers.tooltips.hideAll();

        firstLevelMenuNode.querySelectorAll('.ibexa-main-menu__item[data-item-name]').forEach((item) => {
            const labelNode = item.querySelector('.ibexa-main-menu__item-text-column');
            const actionNode = item.querySelector(
                isMenuExpanded()
                    ? '.ibexa-main-menu__item-action'
                    : '.ibexa-main-menu__item-action--popup-trigger, .ibexa-main-menu__item-action:not(.ibexa-main-menu__item-action--accordion-trigger)',
            );

            if (!labelNode || !actionNode) {
                return;
            }

            if (firstLevelMenuNode.classList.contains('ibexa-main-menu__navbar--collapsed')) {
                actionNode.setAttribute('title', labelNode.textContent.trim());
                actionNode.setAttribute('data-tooltip-placement', 'right');
                actionNode.setAttribute('data-tooltip-extra-class', 'ibexa-tooltip--navigation');
            } else {
                global.bootstrap?.Tooltip.getInstance(actionNode)?.dispose();
                actionNode.removeAttribute('title');
                actionNode.removeAttribute('data-tooltip-placement');
                actionNode.removeAttribute('data-tooltip-extra-class');
                actionNode.removeAttribute('data-original-title');
                actionNode.removeAttribute('data-bs-original-title');
            }
        });

        ibexa.helpers.tooltips.parse(mainMenuNode);
    };
    const getAccordionInstance = (accordionNode) => {
        try {
            return objectInstances.getInstance(accordionNode);
        } catch (error) {
            return null;
        }
    };
    const getAccordionNode = (itemNode) => itemNode.querySelector('.ids-accordion');
    const getAccordionExpanderNode = (itemNode) => itemNode.querySelector('.ids-expander');
    const setAccordionExpandedFallback = (accordionNode, isExpanded) => {
        const expanderNode = accordionNode.querySelector('.ids-expander');
        const contentNode = accordionNode.querySelector('.ids-accordion__content');

        accordionNode.classList.toggle('ids-accordion--is-expanded', isExpanded);
        accordionNode.classList.toggle('ids-accordion--is-animating', false);

        if (contentNode && !isExpanded) {
            contentNode.style.height = '0px';
        }

        if (!expanderNode) {
            return;
        }

        expanderNode.classList.toggle('ids-expander--is-expanded', isExpanded);
        expanderNode.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    };
    const setAccordionExpanded = (itemNode, isExpanded) => {
        const accordionNode = getAccordionNode(itemNode);

        if (!accordionNode) {
            return;
        }

        const accordionInstance = getAccordionInstance(accordionNode);
        const accordionExpanderNode = getAccordionExpanderNode(itemNode);
        const isCurrentlyExpanded = accordionNode.classList.contains('ids-accordion--is-expanded');

        if (accordionInstance) {
            accordionInstance.toggleIsExpanded(isExpanded);
        } else if (accordionExpanderNode && isCurrentlyExpanded !== isExpanded) {
            accordionExpanderNode.click();
        } else {
            setAccordionExpandedFallback(accordionNode, isExpanded);
        }

        itemNode.classList.toggle('ibexa-main-menu__item--expanded', isExpanded);
    };
    const closeAllAccordions = (exceptItemName = null) => {
        firstLevelNavItems.forEach((itemNode) => {
            if (itemNode.dataset.hasChildren !== 'true' || itemNode.dataset.itemName === exceptItemName) {
                return;
            }

            const accordionNode = getAccordionNode(itemNode);
            const accordionExpanderNode = getAccordionExpanderNode(itemNode);

            if (!accordionNode?.classList.contains('ids-accordion--is-expanded')) {
                return;
            }

            if (accordionExpanderNode) {
                accordionExpanderNode.click();

                return;
            }

            setAccordionExpanded(itemNode, false);
        });
    };
    const toggleAccordion = (itemName) => {
        const itemNode = firstLevelMenuNode.querySelector(`.ibexa-main-menu__item[data-item-name="${CSS.escape(itemName)}"]`);

        if (!itemNode || itemNode.dataset.hasChildren !== 'true') {
            return;
        }

        const accordionNode = getAccordionNode(itemNode);
        const accordionExpanderNode = getAccordionExpanderNode(itemNode);
        const shouldExpand = !accordionNode?.classList.contains('ids-accordion--is-expanded');

        if (shouldExpand) {
            closeAllAccordions(itemName);
        }

        if (accordionExpanderNode) {
            accordionExpanderNode.click();

            return;
        }

        setAccordionExpanded(itemNode, shouldExpand);
    };
    const closeAllPopups = (exceptItemName = null) => {
        popupMenuInstances.forEach((popupMenuInstance, itemName) => {
            const isExpanded = !popupMenuInstance.popupMenuElement.classList.contains('ibexa-popup-menu--hidden');

            if (itemName !== exceptItemName && isExpanded) {
                popupMenuInstance.handleToggle();
            }
        });
    };
    const syncActiveItems = (itemName) => {
        firstLevelNavItems.forEach((itemNode) => {
            const isCurrentItem = itemNode.dataset.itemName === itemName;

            itemNode.querySelectorAll('.ibexa-main-menu__item-action').forEach((actionNode) => {
                actionNode.classList.toggle('active', isCurrentItem);
            });
        });
    };
    const openCurrentAccordion = () => {
        const currentItemNode = firstLevelMenuNode.querySelector(
            '.ibexa-main-menu__item[data-has-children="true"] .ibexa-main-menu__item-action--selected',
        )?.closest('.ibexa-main-menu__item[data-item-name]');

        if (!currentItemNode) {
            return;
        }

        setAccordionExpanded(currentItemNode, true);
    };
    const setMenuExpanded = (isExpanded) => {
        firstLevelMenuNode.classList.toggle('ibexa-main-menu__navbar--collapsed', !isExpanded);
        syncExpandToggleButtonState(isExpanded);

        if (isExpanded) {
            closeAllPopups();
            closeAllAccordions();
            openCurrentAccordion();
        } else {
            closeAllAccordions();
        }

        parseMenuTitles();
    };
    const parsePopup = (button) => {
        const { popupTargetSelector } = button.dataset;
        const popupNode = doc.querySelector(popupTargetSelector);

        if (!popupNode) {
            return;
        }

        const popupMenuInstance = new ibexa.core.PopupMenu({
            popupMenuElement: popupNode,
            triggerElement: button,
            position: () => {
                const gap = 12;
                const viewportGap = 8;
                const buttonRect = button.getBoundingClientRect();
                const popupHeight = popupNode.offsetHeight;
                const minTop = global.scrollY + viewportGap;
                const maxTop = global.scrollY + global.innerHeight - popupHeight - viewportGap;
                let top = buttonRect.top + global.scrollY;

                if (buttonRect.top + popupHeight > global.innerHeight - viewportGap) {
                    top = buttonRect.bottom + global.scrollY - popupHeight;
                }

                top = Math.max(minTop, Math.min(top, maxTop));

                popupNode.style.top = `${top}px`;
                popupNode.style.left = `${buttonRect.right + global.scrollX + gap}px`;
            },
        });
        const { itemName } = button.closest('.ibexa-main-menu__item').dataset;

        popupMenuInstances.set(itemName, popupMenuInstance);
        button.addEventListener(
            'click',
            () => {
                if (!isMenuExpanded()) {
                    const tooltipInstance = global.bootstrap?.Tooltip.getInstance(button);

                    tooltipInstance?.hide();
                    tooltipInstance?.disable();
                    button.addEventListener(
                        'mouseleave',
                        () => {
                            tooltipInstance?.enable();
                        },
                        { once: true },
                    );
                }
            },
            false,
        );
        button.addEventListener('click', () => closeAllPopups(itemName), false);
    };
    const handleParentItemClick = (event) => {
        const itemNode = event.currentTarget.closest('.ibexa-main-menu__item[data-item-name]');

        if (!itemNode) {
            return;
        }

        const { itemName } = itemNode.dataset;

        event.preventDefault();

        if (isMenuExpanded()) {
            closeAllPopups();
            toggleAccordion(itemName);

            return;
        }

        closeAllAccordions();
    };
    const handleAccordionExpanderClick = ({ currentTarget }) => {
        const itemNode = currentTarget.closest('.ibexa-main-menu__item[data-item-name]');

        if (!itemNode) {
            return;
        }

        const { itemName } = itemNode.dataset;
        const shouldExpand = !getAccordionNode(itemNode)?.classList.contains('ids-accordion--is-expanded');

        closeAllPopups();

        if (shouldExpand) {
            closeAllAccordions(itemName);
        }

        itemNode.classList.toggle('ibexa-main-menu__item--expanded', shouldExpand);
    };

    syncExpandToggleButtonState(isMenuExpanded());
    parseMenuTitles();
    parentItemButtons.forEach((button) => button.addEventListener('click', handleParentItemClick, false));
    popupTriggerButtons.forEach(parsePopup);
    firstLevelMenuNode.querySelectorAll('.ids-expander').forEach((button) => {
        button.addEventListener('click', handleAccordionExpanderClick, false);
    });
    firstLevelMenuNode.addEventListener(
        'transitionend',
        (event) => {
            if (event.propertyName === 'width') {
                doc.body.dispatchEvent(new CustomEvent('ibexa-content-resized'));
            }
        },
        false,
    );
    expandToggleButton.addEventListener('click', () => setMenuExpanded(!isMenuExpanded()), false);

    firstLevelNavItems.forEach((itemNode) => {
        const accordionNode = itemNode.querySelector('.ids-accordion');

        if (accordionNode?.classList.contains('ids-accordion--is-expanded')) {
            itemNode.classList.add('ibexa-main-menu__item--expanded');
        }
    });

})(window, window.document, window.ibexa);
