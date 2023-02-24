(function (global, doc) {
    const headerSearchInput = doc.querySelector('.ibexa-main-header__search');
    const headerSearchSubmitBtn = doc.querySelector('.ibexa-main-header .ibexa-input-text-wrapper__action-btn--search');
    const searchForm = doc.querySelector('.ibexa-search-form');
    const searchInput = doc.querySelector('.ibexa-search-form__search-input');
    const languageSelector = doc.querySelector('.ibexa-filters__item--language-selector .ibexa-filters__select');
    const submitForm = () => {
        searchInput.value = headerSearchInput.value;
        searchForm.submit();
    };
    const handleHeaderSearchBtnClick = (event) => {
        event.preventDefault();

        submitForm();
    };

    headerSearchInput.value = searchInput.value;

    headerSearchSubmitBtn.addEventListener('click', handleHeaderSearchBtnClick, false);
    languageSelector?.addEventListener('change', submitForm, false);
})(window, document);
