(function (global, doc, ibexa, Routing) {
    const MIN_SEARCH_TEXT_LENGTH = 3;
    const RESULTS_LIMIT = 5;
    const globalSearch = doc.querySelector('.ibexa-global-search');
    const { getJsonFromResponse } = window.ibexa.helpers.request;
    const { escapeHTML } = window.ibexa.helpers.text;

    if (!globalSearch) {
        return;
    }

    const globalSearchInput = globalSearch.querySelector('.ibexa-global-search__input');
    const clearBtn = globalSearch.querySelector(' .ibexa-input-text-wrapper__action-btn--clear');
    const autocompleteNode = globalSearch.querySelector('.ibexa-global-search__autocomplete');
    const autocompleteListNode = globalSearch.querySelector('.ibexa-global-search__autocomplete-list');
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const contentTypeHelper = ibexa.helpers.contentType;
    let controller;
    const highlightSearchText = (searchText, string) => {
        const stringLowerCase = string.toLowerCase();
        const searchTextLowerCase = searchText.toLowerCase();
        const matches = stringLowerCase.matchAll(searchTextLowerCase);
        const stringArray = [];
        let previousIndex = 0;

        for (const match of matches) {
            const endOfSearchTextIndex = match.index + searchText.length;
            const autocompleteHighlightTemplate = autocompleteListNode.dataset.templateHighlight;
            const renderedTemplate = autocompleteHighlightTemplate.replace(
                '{{ highlightText }}',
                escapeHTML(string.slice(match.index, endOfSearchTextIndex)),
            );

            stringArray.push(escapeHTML(string.slice(previousIndex, match.index)));
            stringArray.push(renderedTemplate);

            previousIndex = match.index + searchText.length;
        }

        stringArray.push(escapeHTML(string.slice(previousIndex)));

        return stringArray.join('');
    };
    const showResults = (searchText, results) => {
        const fragment = doc.createDocumentFragment();

        results.forEach((result) => {
            const container = doc.createElement('ul');
            const location = result.value.Location;
            const content = location.ContentInfo.Content;
            const autocompleteItemTemplate = autocompleteListNode.dataset.templateItem;
            const renderedTemplate = autocompleteItemTemplate
                .replace('{{ contentName }}', highlightSearchText(searchText, content.TranslatedName))
                .replace('{{ iconHref }}', contentTypeHelper.getContentTypeIconUrlByHref(content.ContentType._href))
                .replace('{{ contentTypeName }}', escapeHTML(contentTypeHelper.getContentTypeNameByHref(content.ContentType._href)))
                .replaceAll('{{ contentBreadcrumbs }}', 'tu / będzie / ładny / breadcrumb')
                .replace('{{ contentHref }}', Routing.generate('ibexa.content.view', { contentId: content._id, locationId: location.id }));

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
        const query = { FullTextCriterion: `${searchText}*` };
        const body = JSON.stringify({
            ViewInput: {
                identifier: `global-search-query-${query.FullTextCriterion}`,
                public: false,
                languageCode: null,
                useAlwaysAvailable: true,
                LocationQuery: {
                    FacetBuilders: {},
                    SortClauses: {},
                    Query: query,
                    limit: RESULTS_LIMIT,
                    offset: 0,
                },
            },
        });
        const request = new Request('/api/ibexa/v2/views', {
            method: 'POST',
            headers: {
                Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
                'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
                'X-Siteaccess': siteaccess,
                'X-CSRF-Token': token,
            },
            body,
            mode: 'same-origin',
            credentials: 'same-origin',
        });
        controller = new AbortController();
        const { signal } = controller;

        fetch(request, { signal })
            .then(getJsonFromResponse)
            .then((response) => response.View.Result.searchHits.searchHit)
            .then(showResults.bind(this, searchText))
            .catch(() => {});
    };
    const handleTyping = (event) => {
        const searchText = event.currentTarget.value.trim();

        if (controller) {
            controller.abort();
        }

        if (searchText.length <= MIN_SEARCH_TEXT_LENGTH) {
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

        if (focusedItemElement?.parentElement?.previousElementSibling) {
            focusedItemElement.parentElement.previousElementSibling.firstElementChild.focus();
        }
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
        removeKeyboardEventListener;
    };

    globalSearchInput.addEventListener('keyup', handleTyping, false);
    clearBtn.addEventListener('click', hideAutocomplete, false);
})(window, document, window.ibexa, window.Routing);
