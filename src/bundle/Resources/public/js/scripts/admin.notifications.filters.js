(function (global, doc) {
    const searchForm = doc.querySelector('.ibexa-list-search-form');
    const filtersContainerNode = doc.querySelector('.ibexa-list-filters');
    const applyFiltersBtn = filtersContainerNode.querySelector('.ibexa-btn--apply');
    const clearFiltersBtn = filtersContainerNode.querySelector('.ibexa-btn--clear');
    const statusFilterNode = filtersContainerNode.querySelector('.ibexa-list-filters__item--statuses');
    const typeFilterNode = filtersContainerNode.querySelector('.ibexa-list-filters__item--type');
    const datetimeFilterNodes = filtersContainerNode.querySelectorAll('.ibexa-list-filters__item--date-time .ibexa-picker');

    const clearFilter = (filterNode) => {
        if (!filterNode) {
            return;
        }

        const sourceSelect = filterNode.querySelector('.ibexa-list-filters__item-content .ibexa-dropdown__source .ibexa-input--select');
        const sourceSelectOptions = sourceSelect?.querySelectorAll('option');
        const checkboxes = filterNode.querySelectorAll(
            '.ibexa-list-filters__item-content .ibexa-input--checkbox:not([name="dropdown-checkbox"])',
        );
        const timePicker = filterNode.querySelector('.ibexa-date-time-picker__input');

        if (sourceSelect) {
            sourceSelectOptions.forEach((option) => (option.selected = false));

            if (isNodeTimeFilter(filterNode)) {
                sourceSelectOptions[0].selected = true;
            }
        } else if (checkboxes.length) {
            checkboxes.forEach((checkbox) => (checkbox.checked = false));
        } else if (timePicker.value.length) {
            const formInput = filterNode.querySelector('.ibexa-picker__form-input');

            timePicker.value = '';
            formInput.value = '';

            timePicker.dispatchEvent(new Event('input'));
            formInput.dispatchEvent(new Event('input'));
        }

        searchForm.submit();
    };
    const attachFilterEvents = (filterNode) => {
        if (!filterNode) {
            return;
        }

        const sourceSelect = filterNode.querySelector('.ibexa-list-filters__item-content .ibexa-dropdown__source .ibexa-input--select');
        const checkboxes = filterNode.querySelectorAll(
            '.ibexa-list-filters__item-content .ibexa-input--checkbox:not([name="dropdown-checkbox"])',
        );
        const picker = filterNode.querySelector('.ibexa-input--date');

        picker?.addEventListener('change', filterChange, false);
        sourceSelect?.addEventListener('change', filterChange, false);
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', filterChange, false);
        });
    };
    const isNodeTimeFilter = (filterNode) => {
        return filterNode.classList.contains('ibexa-picker');
    };
    const hasFilterValue = (filterNode) => {
        if (!filterNode) {
            return;
        }

        const select = filterNode.querySelector('.ibexa-dropdown__source .ibexa-input--select');
        const checkedCheckboxes = filterNode.querySelectorAll('.ibexa-input--checkbox:checked');

        if (isNodeTimeFilter(filterNode)) {
            const timePicker = filterNode.querySelector('.ibexa-date-time-picker__input');

            return !!timePicker.dataset.timestamp;
        }

        return !!(select?.value || checkedCheckboxes?.length);
    };
    const isSomeFilterSet = () => {
        const hasStatusFilterValue = hasFilterValue(statusFilterNode);
        const hasTypeFilterValue = hasFilterValue(typeFilterNode);
        const hasDatetimeFilterValue = [...datetimeFilterNodes].some((input) => hasFilterValue(input));

        return hasStatusFilterValue || hasTypeFilterValue || hasDatetimeFilterValue;
    };
    const attachInitEvents = () => {
        attachFilterEvents(statusFilterNode);
        attachFilterEvents(typeFilterNode);
        datetimeFilterNodes.forEach((input) => attachFilterEvents(input));
    };
    const filterChange = () => {
        const hasFiltersSetValue = isSomeFilterSet();

        applyFiltersBtn.disabled = false;
        clearFiltersBtn.disabled = !hasFiltersSetValue;
    };
    const clearAllFilters = () => {
        clearFilter(statusFilterNode);
        clearFilter(typeFilterNode);
        datetimeFilterNodes.forEach((input) => clearFilter(input));
    };

    attachInitEvents();

    clearFiltersBtn.addEventListener('click', clearAllFilters, false);
})(window, window.document);
