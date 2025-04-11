import { getRestInfo } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

(function (global, doc, ibexa) {
    const MIN_QUERY_LENGTH = 3;

    class SuggestionTaggify extends ibexa.core.Taggify {
        constructor(config) {
            super(config);

            const { siteaccess, token } = getRestInfo();

            this.suggestionsListNode = config.suggestionsListNode ?? this.container.querySelector('.ibexa-taggify__suggestions');
            this.token = config.token ?? token;
            this.siteaccess = config.siteaccess ?? siteaccess;

            this.renderSuggestionsList = this.renderSuggestionsList.bind(this);
            this.getItemsFromResponse = this.getItemsFromResponse.bind(this);
        }

        hideSuggestionsList() {
            this.suggestionsListNode.classList.add('ibexa-taggify__suggestions--hidden');
        }

        showSuggestionsList() {
            this.suggestionsListNode.classList.remove('ibexa-taggify__suggestions--hidden');
        }

        createSuggestionsRequestBody(query) {
            return JSON.stringify({
                ViewInput: {
                    identifier: `find-suggestions-${query}`,
                    public: false,
                    ContentQuery: {
                        FacetBuilders: {},
                        SortClauses: {},
                        Query: {
                            FullTextCriterion: `${query}*`,
                            ContentTypeIdentifierCriterion: ibexa.adminUiConfig.userContentTypes,
                        },
                        limit: 10,
                        offset: 0,
                    },
                },
            });
        }

        createSuggestionsRequest(body) {
            return new Request('/api/ibexa/v2/views', {
                method: 'POST',
                headers: {
                    Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
                    'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
                    'X-Siteaccess': this.siteaccess,
                    'X-CSRF-Token': this.token,
                },
                body,
                mode: 'same-origin',
                credentials: 'same-origin',
            });
        }

        getSuggestions(query) {
            const body = this.createSuggestionsRequestBody(query);
            const request = this.createSuggestionsRequest(body);

            fetch(request)
                .then(ibexa.helpers.request.getJsonFromResponse)
                .then(this.getItemsFromResponse)
                .then(this.renderSuggestionsList)
                .catch(ibexa.helpers.notification.showErrorNotification);
        }

        getItemsFromResponse(response) {
            return response.View.Result.searchHits.searchHit.map((hit) => hit.value.Content);
        }

        renderSuggestionsList(items) {
            const fragment = doc.createDocumentFragment();

            items.forEach((item) => {
                const listItemNode = this.renderSuggestionItem(item);

                listItemNode.addEventListener(
                    'click',
                    ({ currentTarget }) => {
                        this.addTag(currentTarget.innerHTML, item);
                        this.hideSuggestionsList();

                        this.inputNode.value = '';
                    },
                    false,
                );

                fragment.append(listItemNode);
            });

            this.suggestionsListNode.innerHTML = '';
            this.suggestionsListNode.append(fragment);

            this.showSuggestionsList();
        }

        renderSuggestionItem(item) {
            const itemTemplate = this.suggestionsListNode.dataset.template;
            const renderedTemplate = itemTemplate.replace('{{ name }}', item.TranslatedName);
            const container = doc.createElement('div');

            container.innerHTML = '';
            container.insertAdjacentHTML('beforeend', renderedTemplate);

            return container.querySelector('div');
        }

        handleInputKeyUp(event) {
            super.handleInputKeyUp(event);

            if (this.isAcceptKeyPressed(event.key)) {
                this.hideSuggestionsList();

                return;
            }

            if (this.inputNode.value.length > MIN_QUERY_LENGTH) {
                this.getSuggestions(this.inputNode.value);
            }
        }

        init() {
            super.init();

            this.inputNode.addEventListener('input', ({ currentTarget }) => {
                if (currentTarget.value.length < MIN_QUERY_LENGTH) {
                    this.hideSuggestionsList();
                }
            });
        }
    }

    ibexa.addConfig('core.SuggestionTaggify', SuggestionTaggify);
})(window, window.document, window.ibexa);
