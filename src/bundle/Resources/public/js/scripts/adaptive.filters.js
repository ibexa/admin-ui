(function (global, doc, ibexa) {
    const adaptiveFilters = doc.querySelectorAll('.ibexa-adaptive-filters');
    const initializeAdaptiveFilters = (adaptiveFilter) => {
        const adaptiveItemsContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__items');
        const adaptiveItemsCollapsibleContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__collapsible');
        const actionsContainer = adaptiveFilter.querySelector('.ibexa-adaptive-filters__actions');
        const toggleBtn = adaptiveFilter.querySelector('.ibexa-adaptive-filters__toggler');
        const collapse = global.bootstrap.Collapse.getOrCreateInstance(adaptiveItemsCollapsibleContainer, {
            toggle: false,
        });
        const adaptiveItems = new ibexa.core.AdaptiveItems({
            itemHiddenClass: 'ibexa-adaptive-filters__item--hidden',
            container: adaptiveItemsContainer,
            getActiveItem: () => null,
            prepareItemsBeforeAdapt: () => {
                [...adaptiveItemsCollapsibleContainer.children].forEach((child) =>
                    adaptiveItemsContainer.insertBefore(child, actionsContainer),
                );
            },
            onAdapted: (visibleItems, hiddenItems) => {
                if (hiddenItems.size === 0) {
                    collapse.hide();
                }

                hiddenItems.forEach((hiddenItem) => adaptiveItemsCollapsibleContainer.append(hiddenItem));
            },
        });
        adaptiveItemsCollapsibleContainer.addEventListener('hide.bs.collapse', () => {
            toggleBtn.classList.add('ibexa-adaptive-filters__toggler--collapsed');
        });
        adaptiveItemsCollapsibleContainer.addEventListener('show.bs.collapse', () => {
            toggleBtn.classList.remove('ibexa-adaptive-filters__toggler--collapsed');
        });
        adaptiveItems.init();
    };

    adaptiveFilters.forEach((adaptiveFilter) => initializeAdaptiveFilters(adaptiveFilter));
})(window, window.document, window.ibexa);
