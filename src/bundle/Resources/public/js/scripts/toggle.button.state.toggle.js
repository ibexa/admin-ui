(function (global, doc, eZ) {
    doc.querySelectorAll('.ibexa-toggle').forEach((toggleNode) => {
        if (toggleNode.ibexaInstance) {
            return;
        }

        toggleNode.ibexaInstance = new eZ.core.ToggleButton({ toggleNode });

        toggleNode.ibexaInstance.init();
    });
})(window, window.document, window.eZ);
