(function (global, doc) {
    let isExternalProtocolNavigation = false;

    doc.addEventListener(
        'click',
        (event) => {
            const link = event.target.closest('a[href]');

            isExternalProtocolNavigation = !!link && !['http:', 'https:'].includes(link.protocol);
        },
        true,
    );

    global.onbeforeunload = () => {
        if (isExternalProtocolNavigation) {
            isExternalProtocolNavigation = false;

            return null;
        }

        doc.querySelector('body').classList.add('ibexa-prevent-click');

        return null;
    };

    global.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            doc.querySelector('body').classList.remove('ibexa-prevent-click');
        }
    });
})(window, window.document);
