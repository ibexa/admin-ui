(function (document) {
    const searchForm = document.querySelector('.ibexa-search-form') as HTMLFormElement | null;
    const searchInput = document.querySelector('.ibexa-search-form__search-input') as HTMLInputElement | null;
    const headerSearchInput = document.querySelector('.ibexa-global-search__input') as HTMLInputElement | null;
    const languageSelector = document.querySelector('.ibexa-filters__item--language-selector .ibexa-filters__select') as HTMLSelectElement | null;
    const headerSearchSubmitBtn = document.querySelector('.ibexa-main-header .ibexa-input-text-wrapper__action-btn--search') as HTMLButtonElement | null;

    if (!headerSearchInput || !searchInput || !searchForm) {
        return;
    }

    const submitForm = () => {
        searchInput.value = headerSearchInput.value;
        searchForm.submit();
    };
    const handleHeaderSearchBtnClick = (event: MouseEvent) => {
        event.preventDefault();

        submitForm();
    };

    headerSearchInput.value = searchInput.value;

    headerSearchSubmitBtn?.addEventListener('click', handleHeaderSearchBtnClick, false);
    languageSelector?.addEventListener('change', submitForm, false);
})(document);
