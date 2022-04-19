(function (global, doc, ibexa) {
    const getId = () => doc.querySelector('meta[name="UserId"]').content;

    ibexa.addConfig('helpers.user', {
        getId,
    });
})(window, window.document, window.ibexa);
