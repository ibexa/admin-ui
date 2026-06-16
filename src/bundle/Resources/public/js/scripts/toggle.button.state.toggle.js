(function (global, doc, ibexa) {
    doc.querySelectorAll('.ibexa-toggle').forEach((toggleNode) => {
        if (toggleNode.ibexaInstance) {
            return;
        }

        toggleNode.ibexaInstance = new ibexa.core.ToggleButton({ toggleNode });

        toggleNode.ibexaInstance.init();
    });
})(window, window.document, window.ibexa);
