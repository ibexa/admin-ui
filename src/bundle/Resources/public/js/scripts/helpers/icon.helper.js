(function (global, doc, ibexa) {
    const getIconPath = (path, iconSet = ibexa.adminUiConfig.iconPaths.defaultIconSet) => {
        const iconSetPath = ibexa.adminUiConfig.iconPaths.iconSets[iconSet];

        return `${iconSetPath}#${path}`;
    };

    ibexa.addConfig('helpers.icon', {
        getIconPath,
    });
})(window, window.document, window.ibexa);
