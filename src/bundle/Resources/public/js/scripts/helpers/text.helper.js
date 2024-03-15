(function (global, doc, ibexa) {
    const escapeHTML = (string) => {
        const stringTempNode = doc.createElement('div');

        stringTempNode.appendChild(doc.createTextNode(string));

        return stringTempNode.innerHTML;
    };

    const escapeDataset = (str) => {
        if (str === null) {
            return '';
        }

        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    ibexa.addConfig('helpers.text', {
        escapeHTML,
        escapeDataset,
    });
})(window, window.document, window.ibexa);
