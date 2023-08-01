(function (global, doc, ibexa) {
    const splitBtnsContainers = doc.querySelectorAll('.ibexa-split-btn');

    splitBtnsContainers.forEach((container) => {
        const splitBtn = new ibexa.core.SplitBtn({ container });

        splitBtn.init();
    });
})(window, window.document, window.ibexa);
