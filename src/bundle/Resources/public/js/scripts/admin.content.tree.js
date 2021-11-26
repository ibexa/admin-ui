(function (global, doc, React, ReactDOM, eZ) {
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const contentTreeContainer = doc.querySelector('.ibexa-content-tree-container');
    const contentTreeRootElement = doc.querySelector('.ibexa-content-tree-container__root');
    const userId = window.eZ.helpers.user.getId();
    const removeContentTreeContainerWidth = () => {
        contentTreeContainer.style.width = null;
    }
    const addContentTreeListeners = () => {
        if (!contentTreeContainer) {
            return;
        }

        doc.body.addEventListener('ibexa-tb-rendered:ibexa-content-tree', removeContentTreeContainerWidth);
    }
    const renderTree = () => {
        if (!contentTreeContainer) {
            return;
        }

        const { currentLocationPath, treeRootLocationId } = contentTreeContainer.dataset;

        ReactDOM.render(
            React.createElement(eZ.modules.ContentTree, {
                userId,
                currentLocationPath,
                rootLocationId: parseInt(treeRootLocationId, 10),
                restInfo: { token, siteaccess },
            }),
            contentTreeRootElement
        );
    }

    addContentTreeListeners();
    renderTree();
})(window, window.document, window.React, window.ReactDOM, window.eZ);
