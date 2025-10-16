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

    const dangerouslyAppend = (node, nodeOrText) => {
        node.append(nodeOrText);
    };

    eZ.addConfig('helpers.dom', {
        safelySetInnerHTML,
        dangerouslySetInnerHTML,
        dangerouslyInsertAdjacentHTML,
        dangerouslyAppend,
    });
})(window, window.document, window.eZ);
