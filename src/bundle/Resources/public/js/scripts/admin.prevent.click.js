(function(global, doc) {
    global.onbeforeunload = () => {
        doc.querySelector('body').classList.add('ibexa-prevent-click');

        return null;
    };
})(window, window.document);
