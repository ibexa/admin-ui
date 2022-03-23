(function (global, doc) {
    const sidebarFilters = doc.querySelectorAll('.ibexa-sidebar-filter');
    const sidebarFiltersHeader = doc.querySelector('.ibexa-sidebar-filter__header');

    const toggleSearch = ({ currentTarget }) => {
        const filterHeaderNode = currentTarget.closest('.ibexa-sidebar-filter__header');
        const filterLabelNode = filterHeaderNode.querySelector('.ibexa-sidebar-filter__header-label');
        const filterSearchInput = filterHeaderNode.querySelector('.ibexa-sidebar-filter__search-input');
        const filterActionBtns = filterHeaderNode.querySelectorAll('.ibexa-sidebar-filter__header-actions .ibexa-btn:not(.accordion-button)');

        filterLabelNode.classList.toggle('ibexa-sidebar-filter__header-label--hidden');
        filterSearchInput.classList.toggle('ibexa-sidebar-filter__search-input--hidden');
        filterActionBtns.forEach((actionBtn) => {
            actionBtn.classList.toggle('ibexa-btn--hidden');
        });
    }

    const attachEvents = (sidebarFilterNode) => {
        const toggleSearchBtn = sidebarFilterNode.querySelector('.ibexa-sidebar-filter__search-input-toggle');

        toggleSearchBtn.addEventListener('click', toggleSearch, false);
    }

    sidebarFilters.forEach((sidebarFilter) => {
        attachEvents(sidebarFilter);
    });

    // doc.addEventListener('click', ({ target }) => {
    //     const isClickOusideHeader = [...sidebarFiltersHeader].every((filterHeader) => {
    //         filterHeader.contains(target);
    //     });
        
    //     console.log(isClickOusideHeader);
    // }, false);
})(window, window.document);