import { getInstance, hasInstance } from '@ibexa-design-system/src/bundle/Resources/public/ts/helpers/object.instances';

(function (global, doc, ibexa) {
    const MENU_EXPANDED_COOKIE_NAME = 'ibexa-aui_menu-is-expanded';
    const mainMenuNode = doc.querySelector('.ibexa-main-menu');

    if (!mainMenuNode) {
        return;
    }

    const navbar = mainMenuNode.querySelector('.ibexa-main-menu__navbar');
    const expandToggleBtn = navbar.querySelector('.ibexa-main-menu__expand-toggler');
    const menuItems = [...navbar.querySelectorAll('.ibexa-main-menu__item[data-item-name]')];
    const parentItemBtns = navbar.querySelectorAll(
        '.ibexa-main-menu__item-action--popup-trigger, .ibexa-main-menu__item-action--accordion-trigger',
    );
    const popupTriggerBtns = navbar.querySelectorAll('.ibexa-main-menu__item-action--popup-trigger');
    const scrollableSections = [...navbar.querySelectorAll('.ibexa-main-menu__primary-section, .ibexa-main-menu__secondary-section')];
    const popupMenuInstances = new Map();
    const isMenuExpanded = () => !navbar.classList.contains('ibexa-main-menu__navbar--collapsed');
    const syncScrollbarState = () => {
        const hasScrollbar = scrollableSections.some((section) => section.scrollHeight > section.clientHeight);

        navbar.classList.toggle('ibexa-main-menu__navbar--with-scrollbar', hasScrollbar);
    };
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
        const menuExpanded = isMenuExpanded();

        ibexa.helpers.tooltips.hideAll();

        menuItems.forEach((item) => {
            const labelNode = item.querySelector('.ibexa-main-menu__item-text-column');
            const actionNode = item.querySelector('.ibexa-main-menu__item-action:not(.ibexa-main-menu__item-action--accordion-trigger)');

            if (!labelNode || !actionNode) {
                return;
            }

            if (!menuExpanded) {
                actionNode.setAttribute('title', labelNode.textContent.trim());
                actionNode.dataset.tooltipPlacement = 'right';
                actionNode.dataset.tooltipExtraClass = 'ibexa-tooltip--navigation';
                actionNode.dataset.tooltipOffset = '[0, 12]';
            } else {
                global.bootstrap?.Tooltip.getInstance(actionNode)?.dispose();
                actionNode.removeAttribute('title');
                delete actionNode.dataset.tooltipPlacement;
                delete actionNode.dataset.tooltipExtraClass;
                delete actionNode.dataset.tooltipOffset;
                delete actionNode.dataset.originalTitle;
                delete actionNode.dataset.bsOriginalTitle;
            }
        });

        ibexa.helpers.tooltips.parse(mainMenuNode);
    };
    const getAccordionNode = (itemNode) => itemNode.querySelector('.ids-accordion');
    const getAccordionInstance = (itemNode) => {
        const accordionNode = getAccordionNode(itemNode);

        if (!accordionNode || !hasInstance(accordionNode)) {
            return null;
        }

        return getInstance(accordionNode);
    };
    const setAccordionExpanded = (itemNode, isExpanded) => {
        const accordionInstance = getAccordionInstance(itemNode);

        if (accordionInstance && accordionInstance.isExpanded() !== isExpanded) {
            accordionInstance.toggleIsExpanded(isExpanded);
        }
    };
    const closeAllAccordions = (exceptItemName = null) => {
        menuItems.forEach((itemNode) => {
            if (itemNode.dataset.hasChildren !== 'true' || itemNode.dataset.itemName === exceptItemName) {
                return;
            }

            const accordionInstance = getAccordionInstance(itemNode);

            if (!accordionInstance?.isExpanded()) {
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

        const accordionInstance = getAccordionInstance(itemNode);
        const shouldExpand = !accordionInstance?.isExpanded();

        if (shouldExpand) {
            closeAllAccordions(itemName);
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
        ibexa.helpers.cookies.setBackOfficeCookie(MENU_EXPANDED_COOKIE_NAME, isExpanded);

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

        closeAllPopups();
        closeAllAccordions(itemName);
    };

    const scrollbarResizeObserver = new ResizeObserver(syncScrollbarState);

    scrollableSections.forEach((section) => {
        scrollbarResizeObserver.observe(section);
        scrollbarResizeObserver.observe(section.querySelector('.ibexa-main-menu__items-list'));
    });
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
})(window, window.document, window.ibexa);
