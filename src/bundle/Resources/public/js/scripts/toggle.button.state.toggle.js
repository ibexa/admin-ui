(function (global, doc, ibexa) {
    doc.querySelectorAll('.ibexa-toggle').forEach((toggleNode) => {
        const toggleButton = new ibexa.core.ToggleButton({ toggleNode });

        toggleButton.init();
    });
})(window, window.document, window.ibexa);
