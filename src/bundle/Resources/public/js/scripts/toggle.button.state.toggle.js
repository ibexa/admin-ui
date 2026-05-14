(function (global, doc, eZ) {
    const TOGGLE_INITIALIZED_ATTR = 'data-ibexa-toggle-initialized';

    doc.querySelectorAll('.ibexa-toggle').forEach((toggleNode) => {
        if (toggleNode.hasAttribute(TOGGLE_INITIALIZED_ATTR)) {
            return;
        }

        toggleNode.setAttribute(TOGGLE_INITIALIZED_ATTR, 'true');

        const toggleButton = new eZ.core.ToggleButton({ toggleNode });

        toggleButton.init();
    });
})(window, window.document, window.eZ);
