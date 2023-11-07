(function (global, doc, ibexa, Routing, Translator) {
    const globalSearch = doc.querySelector('.ibexa-global-search');
    const { getJsonFromResponse } = ibexa.helpers.request;
    const { showErrorNotification } = ibexa.helpers.notification;
    const { minQueryLength, resultLimit } = ibexa.adminUiConfig.suggestions;

    if (!globalSearch) {
        return;
    }

    const globalSearchInput = globalSearch.querySelector('.ibexa-global-search__input');
    const clearBtn = globalSearch.querySelector(' .ibexa-input-text-wrapper__action-btn--clear');
    const autocompleteNode = globalSearch.querySelector('.ibexa-global-search__autocomplete');
    const autocompleteListNode = globalSearch.querySelector('.ibexa-global-search__autocomplete-list');
    let searchAbortController;
    const showResults = (searchText, results) => {
        const { renderers } = ibexa.autocomplete;
        const fragment = doc.createDocumentFragment();

        results.forEach((result) => {
            const container = doc.createElement('ul');
            const renderer = renderers[result.type];

            if (!renderer) {
                return;
            }

            const renderedTemplate = renderer(result, searchText);

            container.insertAdjacentHTML('beforeend', renderedTemplate);

            const listItemNode = container.querySelector('li');

            fragment.append(listItemNode);
        });

        addClickOutsideEventListener();
        addKeyboardEventListener();

        autocompleteListNode.innerHTML = '';
        autocompleteListNode.append(fragment);

        window.ibexa.helpers.ellipsis.middle.parse(autocompleteListNode);

        autocompleteNode.classList.remove('ibexa-global-search__autocomplete--hidden');
        autocompleteNode.classList.toggle('ibexa-global-search__autocomplete--results-empty', results.length === 0);
    };
    const getAutocompleteList = (searchText) => {
        const url = Routing.generate('ibexa.search.suggestion', { query: searchText, limit: resultLimit });
        const request = new Request(url, {
            mode: 'same-origin',
            credentials: 'same-origin',
        });

        searchAbortController = new AbortController();

        const { signal } = searchAbortController;

        fetch(request, { signal })
            .then(getJsonFromResponse)
            .then(showResults.bind(this, searchText))
            .catch((error) => {
                if (error.name === 'AbortError') {
                    return;
                }

                showErrorNotification(
                    Translator.trans(/*@Desc("Cannot load suggestions")*/ 'autocomplete.request.error', {}, 'ibexa_search'),
                );
            });
    };
    const handleTyping = (event) => {
        const searchText = event.currentTarget.value.trim();

        searchAbortController?.abort();

        if (searchText.length <= minQueryLength) {
            hideAutocomplete();

            return;
        }

        getAutocompleteList(searchText);
    };
    const handleClickOutside = ({ target }) => {
        if (target.closest('.ibexa-global-search')) {
            return;
        }

        hideAutocomplete();
    };
    const addClickOutsideEventListener = () => {
        doc.body.addEventListener('click', handleClickOutside, false);
    };
    const removeClickOutsideEventListener = () => {
        doc.body.removeEventListener('click', handleClickOutside, false);
    };
    const handleKeyboard = ({ code }) => {
        const keyboardDispatcher = {
            ArrowDown: handleArrowDown,
            ArrowUp: handleArrowUp,
        };

        keyboardDispatcher[code]?.();
    };
    const handleArrowDown = () => {
        const focusedItemElement = autocompleteListNode.querySelector('.ibexa-global-search__autocomplete-item-link:focus');
        const focusedViewAllElement = autocompleteNode.querySelector('.ibexa-global-search__autocomplete-view-all .ibexa-btn:focus');

        if (!focusedItemElement && !focusedViewAllElement) {
            autocompleteListNode.firstElementChild.firstElementChild.focus();

            return;
        }

        if (focusedItemElement?.parentElement?.nextElementSibling) {
            focusedItemElement.parentElement.nextElementSibling.firstElementChild.focus();
        } else {
            autocompleteNode.querySelector('.ibexa-global-search__autocomplete-view-all .ibexa-btn').focus();
        }
    };
    const handleArrowUp = () => {
        const focusedItemElement = autocompleteListNode.querySelector('.ibexa-global-search__autocomplete-item-link:focus');
        const focusedViewAllElement = autocompleteNode.querySelector('.ibexa-global-search__autocomplete-view-all .ibexa-btn:focus');

        if (focusedViewAllElement) {
            autocompleteListNode.lastElementChild.firstElementChild.focus();

            return;
        }

        focusedItemElement?.parentElement?.previousElementSibling.firstElementChild.focus();
    };
    const addKeyboardEventListener = () => {
        doc.body.addEventListener('keydown', handleKeyboard, false);
    };
    const removeKeyboardEventListener = () => {
        doc.body.removeEventListener('keydown', handleKeyboard, false);
    };
    const hideAutocomplete = () => {
        autocompleteNode.classList.add('ibexa-global-search__autocomplete--hidden');
        removeClickOutsideEventListener();
        removeKeyboardEventListener();
    };

    globalSearchInput.addEventListener('keyup', handleTyping, false);
    clearBtn.addEventListener('click', hideAutocomplete, false);
})(window, document, window.ibexa, window.Routing, window.Translator);
