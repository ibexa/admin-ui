(function (global, doc) {
    global.onbeforeunload = () => {
        doc.querySelector('body').classList.add('ibexa-prevent-click');

        return null;
    };

    global.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            doc.querySelector('body').classList.remove('ibexa-prevent-click');
        }
    });
})(window, window.document);
