(function (global, doc, ibexa) {
    const buildItemsFromUDWResponse = (udwItems, getId, callback) => {
        const { removeRootFromPathString, findLocationsByIds, buildLocationsBreadcrumbs } = window.ibexa.helpers.location;

        Promise.all(
            udwItems.map(
                (item) =>
                    new Promise((resolve) => {
                        findLocationsByIds(removeRootFromPathString(item.pathString), (locations) => {
                            resolve({
                                id: getId(item),
                                name: buildLocationsBreadcrumbs(locations),
                            });
                        });
                    }),
            ),
        ).then(callback);
    };

    ibexa.addConfig('helpers.tagViewSelect', {
        buildItemsFromUDWResponse,
    });
})(window, window.document, window.ibexa);
