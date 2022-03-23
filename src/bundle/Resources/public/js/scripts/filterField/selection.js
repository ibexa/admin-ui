(function (global, doc) {
    const toggleListBtns = doc.querySelectorAll('.ibexa-sidebar-filter__toggle-list-btn');
    const calculateListHeight = (items_limit_count) => {

    };
    const toggleList = ({ currentTarget }) => {
        const { itemsShowLimit } = currentTarget.dataset;
        const filterBody = currentTarget.closest('.ibexa-sidebar-filter__body');
        const selectionList = filterBody.querySelector('.ibexa-sidebar-filter__selection-list');
        const selectionListItems = selectionList.querySelectorAll('.ibexa-sidebar-filter__selection-list-item');

        selectionListItems.forEach((listItem, index) => {
            const isShortList = selectionList.classList.contains('ibexa-sidebar-filter__selection-list-item--short');
            const isIndexOverLimit = index >= itemsShowLimit;
            const shouldHiddenItem = !isShortList && isIndexOverLimit;

            listItem.classList.toggle('ibexa-sidebar-filter__selection-list-item--hidden', shouldHiddenItem);
        });
        selectionList.classList.toggle('ibexa-sidebar-filter__selection-list-item--short');
    };

    toggleListBtns.forEach((toggleListBtn) => {
        toggleListBtn.addEventListener('click', toggleList, false)
    });
})(window, window.document);