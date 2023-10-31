(function (global, doc, ibexa, Routing) {
    const globalSearch = doc.querySelector('.ibexa-global-search');

    if (!globalSearch) {
        return;
    }

    const { escapeHTML } = ibexa.helpers.text;
    const { highlightText } = ibexa.helpers.highlight;
    const { getContentTypeIconUrl, getContentTypeName } = ibexa.helpers.contentType;
    const autocompleteListNode = globalSearch.querySelector('.ibexa-global-search__autocomplete-list');
    const autocompleteContentTemplateNode = globalSearch.querySelector('.ibexa-global-search__autocomplete-content-template');
    const renderItem = (result, searchText) => {
        const { locationId, contentId, name, contentTypeIdentifier, pathString, parentLocations } = result;
        const pathArray = pathString.split('/').filter((id) => id);

        const breadcrumb = pathArray.reduce((total, pathLocationId, index) => {
            const parentLocation = parentLocations.find((parent) => parent.locationId === parseInt(pathLocationId, 10));

            if (parseInt(pathLocationId, 10) === locationId) {
                return total;
            }

            return index === 0 ? parentLocation.name : `${total} / ${parentLocation.name}`;
        }, '');

        const autocompleteItemTemplate = autocompleteContentTemplateNode.dataset.templateItem;
        const autocompleteHighlightTemplate = autocompleteListNode.dataset.templateHighlight;
        const renderedTemplate = autocompleteItemTemplate
            .replace('{{ contentName }}', highlightText(searchText, name, autocompleteHighlightTemplate))
            .replace('{{ iconHref }}', getContentTypeIconUrl(contentTypeIdentifier))
            .replace('{{ contentTypeName }}', escapeHTML(getContentTypeName(contentTypeIdentifier)))
            .replaceAll('{{ contentBreadcrumbs }}', breadcrumb)
            .replace('{{ contentHref }}', Routing.generate('ibexa.content.view', { contentId, locationId }));

        return renderedTemplate;
    };

    ibexa.addConfig('autocomplete.renderers.content', renderItem, true);
})(window, document, window.ibexa, window.Routing);
