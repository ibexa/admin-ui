(function (global, doc, eZ) {
    const { escapeHTML } = eZ.helpers.text;
    const safelySetInnerHTML = (node, text) => {
        node.innerHTML = escapeHTML(text);
    };

    const dangerouslySetInnerHTML = (node, text) => {
        node.innerHTML = text;
    };

    const dangerouslyInsertAdjacentHTML = (node, position, text) => {
        const escapedText = text;

        node.insertAdjacentHTML(position, escapedText);
    };

    eZ.addConfig('helpers.dom', {
        safelySetInnerHTML,
        dangerouslySetInnerHTML,
        dangerouslyInsertAdjacentHTML,
    });
})(window, window.document, window.eZ);
