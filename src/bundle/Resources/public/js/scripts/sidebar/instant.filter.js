(function (global, doc) {
    let filterTimeout;
    const SELECTOR_ITEM = '.ibexa-instant-filter__group-item';
    const timeout = 200;
    const filters = doc.querySelectorAll('.ibexa-instant-filter');
    const toggleGroupDisplay = (group) => {
        const areChildrenHidden = [...group.querySelectorAll(SELECTOR_ITEM)].every((item) => item.hasAttribute('hidden'));

        group.toggleAttribute('hidden', areChildrenHidden);
    };
    const filterItems = function (itemsMap, groups, event) {
        window.clearTimeout(filterTimeout);

        filterTimeout = window.setTimeout(() => {
            const query = event.target.value.toLowerCase();
            const results = itemsMap.filter((item) => item.label.includes(query));

            itemsMap.forEach((item) => item.element.setAttribute('hidden', true));
            results.forEach((item) => item.element.removeAttribute('hidden'));

            groups.forEach(toggleGroupDisplay);
        }, timeout);
    };
    const initFilter = (filter) => {
        const filterInput = filter.querySelector('.ibexa-instant-filter__input');
        const groups = [...filter.querySelectorAll('.ibexa-instant-filter__group')];
        const items = [...filter.querySelectorAll(SELECTOR_ITEM)];
        const itemsMap = items.reduce(
            (total, item) => [
                ...total,
                {
                    label: item.textContent.toLowerCase(),
                    element: item,
                },
            ],
            [],
        );

        filterInput.addEventListener('change', filterItems.bind(filter, itemsMap, groups), false);
        filterInput.addEventListener('blur', filterItems.bind(filter, itemsMap, groups), false);
        filterInput.addEventListener('keyup', filterItems.bind(filter, itemsMap, groups), false);
        filterInput.addEventListener(
            'keydown',
            (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            },
            false,
        );
    };

    doc.body.addEventListener('ibexa-instant-filters:add-group', (event) => {
        const filterContainer = event.detail.container.closest('.ibexa-instant-filter');

        initFilter(filterContainer);
    });

    filters.forEach(initFilter);
})(window, window.document);
