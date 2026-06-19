(function (global, doc, ibexa) {
    const mainMenuNode = doc.querySelector('.ibexa-main-menu');

    if (!mainMenuNode) {
        return;
    }

    const { objectInstances } = ibexa.helpers;
    const navbar = mainMenuNode.querySelector('.ibexa-main-menu__navbar');
    const expandToggleBtn = navbar.querySelector('.ibexa-main-menu__expand-toggler');
    const firstLevelNavItems = [...navbar.querySelectorAll('.ibexa-main-menu__item[data-item-name]')];
    const parentItemBtns = navbar.querySelectorAll(
        '.ibexa-main-menu__item-action--popup-trigger, .ibexa-main-menu__item-action--accordion-trigger',
    );
    const popupTriggerBtns = navbar.querySelectorAll('.ibexa-main-menu__item-action--popup-trigger');
    const popupMenuInstances = new Map();
    const isMenuExpanded = () => !navbar.classList.contains('ibexa-main-menu__navbar--collapsed');
    const { collapseLabel, expandLabel } = expandToggleBtn.dataset;
    const syncExpandToggleBtnState = (isExpanded) => {
        const tooltipInstance = global.bootstrap?.Tooltip.getInstance(expandToggleBtn);

        expandToggleBtn.classList.toggle('ibexa-main-menu__expand-toggler--collapsed', !isExpanded);
        expandToggleBtn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        expandToggleBtn.setAttribute('aria-label', isExpanded ? collapseLabel : expandLabel);

        if (isExpanded) {
            tooltipInstance?.dispose();
            expandToggleBtn.removeAttribute('title');
        } else {
            expandToggleBtn.title = expandLabel;
        }
    };
    const parseMenuTitles = () => {
        ibexa.helpers.tooltips.hideAll();

        navbar.querySelectorAll('.ibexa-main-menu__item[data-item-name]').forEach((item) => {
            const labelNode = item.querySelector('.ibexa-main-menu__item-text-column');
            const actionNode = item.querySelector(
                isMenuExpanded()
                    ? '.ibexa-main-menu__item-action'
                    : '.ibexa-main-menu__item-action--popup-trigger, .ibexa-main-menu__item-action:not(.ibexa-main-menu__item-action--accordion-trigger)',
            );

            if (!labelNode || !actionNode) {
                return;
            }

            if (navbar.classList.contains('ibexa-main-menu__navbar--collapsed')) {
                actionNode.setAttribute('title', labelNode.textContent.trim());
                actionNode.dataset.tooltipPlacement = 'right';
                actionNode.dataset.tooltipExtraClass = 'ibexa-tooltip--navigation';
            } else {
                global.bootstrap?.Tooltip.getInstance(actionNode)?.dispose();
                actionNode.removeAttribute('title');
                delete actionNode.dataset.tooltipPlacement;
                delete actionNode.dataset.tooltipExtraClass;
                delete actionNode.dataset.originalTitle;
                delete actionNode.dataset.bsOriginalTitle;
            }
        });

        ibexa.helpers.tooltips.parse(mainMenuNode);
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

        const accordionInstance = objectInstances.getInstance(accordionNode);
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
        const itemNode = navbar.querySelector(`.ibexa-main-menu__item[data-item-name="${CSS.escape(itemName)}"]`);

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
    const setMenuExpanded = (isExpanded) => {
        navbar.classList.toggle('ibexa-main-menu__navbar--collapsed', !isExpanded);
        syncExpandToggleBtnState(isExpanded);

        if (isExpanded) {
            closeAllPopups();
            closeAllAccordions();
        } else {
            closeAllAccordions();
        }

        parseMenuTitles();
    };
    const parsePopup = (btn) => {
        const { popupTargetSelector } = btn.dataset;
        const popupNode = doc.querySelector(popupTargetSelector);

        if (!popupNode) {
            return;
        }

        const popupMenuInstance = new ibexa.core.PopupMenu({
            popupMenuElement: popupNode,
            triggerElement: btn,
            position: () => {
                const gap = 12;
                const viewportGap = 8;
                const btnRect = btn.getBoundingClientRect();
                const popupHeight = popupNode.offsetHeight;
                const minTop = global.scrollY + viewportGap;
                const maxTop = global.scrollY + global.innerHeight - popupHeight - viewportGap;
                let top = btnRect.top + global.scrollY;

                if (btnRect.top + popupHeight > global.innerHeight - viewportGap) {
                    top = btnRect.bottom + global.scrollY - popupHeight;
                }

                top = Math.max(minTop, Math.min(top, maxTop));

                popupNode.style.top = `${top}px`;
                popupNode.style.left = `${btnRect.right + global.scrollX + gap}px`;
            },
        });
        const { itemName } = btn.closest('.ibexa-main-menu__item').dataset;

        popupMenuInstances.set(itemName, popupMenuInstance);
        btn.addEventListener(
            'click',
            () => {
                if (!isMenuExpanded()) {
                    const tooltipInstance = global.bootstrap?.Tooltip.getInstance(btn);

                    tooltipInstance?.hide();
                    tooltipInstance?.disable();
                    btn.addEventListener(
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
        btn.addEventListener('click', () => closeAllPopups(itemName), false);
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

    syncExpandToggleBtnState(isMenuExpanded());
    parseMenuTitles();
    parentItemBtns.forEach((btn) => btn.addEventListener('click', handleParentItemClick, false));
    popupTriggerBtns.forEach(parsePopup);
    navbar.querySelectorAll('.ids-expander').forEach((btn) => {
        btn.addEventListener('click', handleAccordionExpanderClick, false);
    });
    navbar.addEventListener(
        'transitionend',
        (event) => {
            if (event.propertyName === 'width') {
                doc.body.dispatchEvent(new CustomEvent('ibexa-content-resized'));
            }
        },
        false,
    );
    expandToggleBtn.addEventListener('click', () => setMenuExpanded(!isMenuExpanded()), false);

    firstLevelNavItems.forEach((itemNode) => {
        const accordionNode = itemNode.querySelector('.ids-accordion');

        if (accordionNode?.classList.contains('ids-accordion--is-expanded')) {
            itemNode.classList.add('ibexa-main-menu__item--expanded');
        }
    });
})(window, window.document, window.ibexa);
