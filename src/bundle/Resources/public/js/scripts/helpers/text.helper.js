(function (global, doc, ibexa) {
    const escapeHTML = (string) => {
        const stringTempNode = doc.createElement('div');

        stringTempNode.appendChild(doc.createTextNode(string));

        return stringTempNode.innerHTML;
    };

    const escapeHTMLAttribute = (string) => {
        if (string === null) {
            return '';
        }

        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    ibexa.addConfig('helpers.text', {
        escapeHTML,
        escapeHTMLAttribute,
    });
})(window, window.document, window.ibexa);
