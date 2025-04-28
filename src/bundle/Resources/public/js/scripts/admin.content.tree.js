(function (global, doc, React, ReactDOMClient, ibexa) {
    const contentTreeContainer = doc.querySelector('.ibexa-content-tree-container');

    if (!contentTreeContainer) {
        return;
    }

    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const contentTreeRootElement = doc.querySelector('.ibexa-content-tree-container__root');
    const { currentLocationPath, treeRootLocationId } = contentTreeContainer.dataset;
    const userId = window.ibexa.helpers.user.getId();
    const removeContentTreeContainerWidth = (event) => {
        if (event.detail.id !== 'ibexa-content-tree') {
            return;
        }

        contentTreeContainer.style.width = null;
    };
    const renderTree = () => {
        const contentTreeRoot = ReactDOMClient.createRoot(contentTreeRootElement);

        contentTreeRoot.render(
            React.createElement(ibexa.modules.ContentTree, {
                userId,
                currentLocationPath,
                rootLocationId: parseInt(treeRootLocationId, 10),
                restInfo: { token, siteaccess },
            }),
        );
    };

    doc.body.addEventListener('ibexa-tb-rendered', removeContentTreeContainerWidth);

    renderTree();
})(window, window.document, window.React, window.ReactDOMClient, window.ibexa);
