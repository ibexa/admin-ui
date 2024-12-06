(function () {
    const searchForm = document.querySelector<HTMLFormElement>('.ibexa-search-form');
    const searchInput = document.querySelector<HTMLInputElement>('.ibexa-search-form__search-input');
    const headerSearchInput = document.querySelector<HTMLInputElement>('.ibexa-global-search__input');
    const languageSelector = document.querySelector<HTMLSelectElement>('.ibexa-filters__item--language-selector .ibexa-filters__select');
    const headerSearchSubmitBtn = document.querySelector<HTMLButtonElement>(
        '.ibexa-main-header .ibexa-input-text-wrapper__action-btn--search',
    );

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
})();
