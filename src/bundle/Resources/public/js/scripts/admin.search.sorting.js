(function (global, doc, ibexa) {
    const searchForm = doc.querySelector('.ibexa-search-form');
    const searchSortOrderSelect = doc.querySelector('.ibexa-search-form__sort-order-select');

    if (searchSortOrderSelect) {
        searchSortOrderSelect.addEventListener(
            'change',
            () => {
                searchForm.submit();
            },
            false,
        );
    }
})(window, window.document, window.ibexa);
