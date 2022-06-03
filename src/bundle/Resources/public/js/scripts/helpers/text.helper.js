(function (global, doc, ibexa) {
    const escapeHTML = (string) => {
        const stringTempNode = doc.createElement('div');

        stringTempNode.appendChild(doc.createTextNode(string));

        return stringTempNode.innerHTML;
    };

    ibexa.addConfig('helpers.text', {
        escapeHTML,
    });
})(window, window.document, window.ibexa);
