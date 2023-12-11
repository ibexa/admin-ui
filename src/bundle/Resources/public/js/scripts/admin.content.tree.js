(function (global, doc, React, ReactDOM, ibexa) {
    const contentTreeContainer = doc.querySelector('.ibexa-content-tree-container');

    if (!contentTreeContainer) {
        return;
    }

    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const contentTreeRootElement = doc.querySelector('.ibexa-content-tree-container__root');
    const { currentLocationPath, treeRootLocationId, header } = contentTreeContainer.dataset;
    const userId = window.ibexa.helpers.user.getId();
    const removeContentTreeContainerWidth = (event) => {
        if (event.detail.id !== 'ibexa-content-tree') {
            return;
        }

        contentTreeContainer.style.width = null;
    };
    const renderTree = () => {
        const contentTreeRoot = ReactDOM.createRoot(contentTreeRootElement);

        contentTreeRoot.render(
            React.createElement(ibexa.modules.ContentTree, {
                userId,
                currentLocationPath,
                rootLocationId: parseInt(treeRootLocationId, 10),
                restInfo: { token, siteaccess },
                header,
            }),
        );
    };

    doc.body.addEventListener('ibexa-tb-rendered', removeContentTreeContainerWidth);

    renderTree();
})(window, window.document, window.React, window.ReactDOM, window.ibexa);
