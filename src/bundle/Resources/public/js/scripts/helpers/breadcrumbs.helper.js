(function(global, doc, ibexa) {
    const getContentBreadcrumbs = (items) => {
        const breadcrumbs = items.map((item) => item.ContentInfo.Content.TranslatedName).join(' / ');

        return ibexa.helpers.text.escapeHTML(breadcrumbs);
    };

    ibexa.addConfig('helpers.breadcrumbs', {
        getContentBreadcrumbs,
    });
})(window, window.document, window.ibexa);
