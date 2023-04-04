(function (global, doc, ibexa, bootstrap) {
    const adaptiveFilters = doc.querySelectorAll('.ibexa-adaptive-filters');
    const initializeAdaptiveFilters = (adaptiveFilter) => {
        const adaptiveItemsContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__items');
        const adaptiveItemsCollapsibleContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__collapsible');
        const adaptiveItemsCollapsibleContentContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__collapsible-content');
        const actionsContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__actions');
        const toggleBtn = adaptiveFilter.querySelector('.ibexa-adaptive-filters__toggler');
        const { expandedInfoCookieName } = adaptiveFilter.dataset;
        const setExpandedInfoCookie = (isExpanded) => {
            const cookieValue = isExpanded ? 'true' : 'false';

            if (expandedInfoCookieName) {
                ibexa.helpers.cookies.setCookie(expandedInfoCookieName, cookieValue);
            }
        };
        const collapse = bootstrap.Collapse.getOrCreateInstance(adaptiveItemsCollapsibleContainer, {
            toggle: false,
        });
        const adaptiveItems = new ibexa.core.AdaptiveItems({
            itemHiddenClass: 'ibexa-adaptive-filters__item--hidden',
            container: adaptiveItemsContainer,
            getActiveItem: () => null,
            prepareItemsBeforeAdapt: () => {
                [...adaptiveItemsCollapsibleContentContainer.children].forEach((child) =>
                    adaptiveItemsContainer.insertBefore(child, actionsContainer),
                );
            },
            onAdapted: (visibleItems, hiddenItems) => {
                if (hiddenItems.size === 0) {
                    collapse.hide();
                }

                hiddenItems.forEach((hiddenItem) => adaptiveItemsCollapsibleContentContainer.append(hiddenItem));

                adaptiveItemsCollapsibleContainer.classList.remove('ibexa-adaptive-filters__collapsible--no-animation')
            },
        });
        adaptiveItemsCollapsibleContainer.addEventListener('hide.bs.collapse', () => {
            toggleBtn.classList.add('ibexa-adaptive-filters__toggler--collapsed');
            adaptiveItemsCollapsibleContainer.classList.add('ibexa-adaptive-filters__collapsible--collapsed');
            adaptiveItemsCollapsibleContentContainer.classList.add('ibexa-adaptive-filters__collapsible-content--collapsed');

            setExpandedInfoCookie(false);
        });
        adaptiveItemsCollapsibleContainer.addEventListener('show.bs.collapse', () => {
            toggleBtn.classList.remove('ibexa-adaptive-filters__toggler--collapsed');
            adaptiveItemsCollapsibleContainer.classList.remove('ibexa-adaptive-filters__collapsible--collapsed');
            adaptiveItemsCollapsibleContentContainer.classList.remove('ibexa-adaptive-filters__collapsible-content--collapsed');

            setExpandedInfoCookie(true);
        });
        adaptiveItems.init();
    };

    adaptiveFilters.forEach(initializeAdaptiveFilters);
})(window, window.document, window.ibexa, window.bootstrap);
