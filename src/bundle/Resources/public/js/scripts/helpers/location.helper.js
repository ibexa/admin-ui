(function (global, doc, ibexa, Translator) {
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const removeRootFromPathString = (pathString) => {
        const pathArray = pathString.split('/').filter((id) => id);

        return pathArray.splice(1, pathArray.length - 1);
    };
    const buildLocationsBreadcrumbs = (locations) =>
        locations.map((Location) => ibexa.helpers.text.escapeHTML(Location.ContentInfo.Content.TranslatedName)).join(' / ');
    const findLocationsByIds = (idList, callback) => {
        const body = JSON.stringify({
            ViewInput: {
                identifier: `locations-by-path-string-${idList.join('-')}`,
                public: false,
                LocationQuery: {
                    FacetBuilders: {},
                    SortClauses: { SectionIdentifier: 'ascending' },
                    Filter: { LocationIdCriterion: idList.join(',') },
                    limit: 50,
                    offset: 0,
                },
            },
        });
        const request = new Request('/api/ibexa/v2/views', {
            method: 'POST',
            headers: {
                Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
                'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
                'X-Requested-With': 'XMLHttpRequest',
                'X-Siteaccess': siteaccess,
                'X-CSRF-Token': token,
            },
            body,
            mode: 'same-origin',
            credentials: 'same-origin',
        });
        const errorMessage = Translator.trans(
            /*@Desc("Cannot find children Locations with ID %idList%")*/ 'select_location.error',
            { idList: idList.join(',') },
            'ibexa_universal_discovery_widget',
        );

        fetch(request)
            .then(ibexa.helpers.request.getJsonFromResponse)
            .then((viewData) => viewData.View.Result.searchHits.searchHit)
            .then((searchHits) => searchHits.map((searchHit) => searchHit.value.Location))
            .then(callback)
            .catch(() => ibexa.helpers.notification.showErrorNotification(errorMessage));
    };

    ibexa.addConfig('helpers.location', {
        removeRootFromPathString,
        findLocationsByIds,
        buildLocationsBreadcrumbs,
    });
})(window, window.document, window.ibexa, window.Translator);
