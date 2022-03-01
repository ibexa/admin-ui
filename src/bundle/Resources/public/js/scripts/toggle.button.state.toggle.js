(function (global, doc, eZ) {
    doc.querySelectorAll('.ibexa-toggle').forEach((toggleNode) => {
        const toggleButton = new eZ.core.ToggleButton({ toggleNode });

        toggleButton.init();
    });
})(window, window.document, window.eZ);
