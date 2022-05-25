(function (global, doc) {
    const dblClickMarkNodes = doc.querySelectorAll('.ibexa-dbl-click-mark');
    const markText = (event) => {
        const targetNode = event.currentTarget;
        const range = doc.createRange();

        range.selectNode(targetNode);
        global.getSelection().removeAllRanges();
        global.getSelection().addRange(range);
    };

    dblClickMarkNodes.forEach((dblClickMarkNode) => {
        dblClickMarkNode.addEventListener('dblclick', markText, false);
    });
})(window, document);
