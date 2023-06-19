(function (global, doc, ibexa) {
    const backdrop = doc.createElement('div');
    const bodyFirstNode = document.body.firstChild;

    backdrop.classList.add('ibexa-backdrop');
    doc.body.insertBefore(backdrop, bodyFirstNode);

    const toggleBackdrop = (shouldBackdropDisplay, extraClass = []) => {
        if (extraClass) {
            backdrop.classList.add(...extraClass);
        } else {
            backdrop.className = 'ibexa-backdrop';
        }

        backdrop.classList.toggle('ibexa-backdrop--active', shouldBackdropDisplay);

        if (shouldBackdropDisplay) {
            document.dispatchEvent(new CustomEvent('ibexa-backdrop:after-show'));
        }
    };

    const show = (extraClass = []) => {
        toggleBackdrop(true, extraClass);
    };

    const hide = () => {
        toggleBackdrop(false, null);
    };

    const get = () => {
        return backdrop;
    };

    ibexa.addConfig('helpers.backdrop', {
        show,
        hide,
        get,
    });
})(window, window.document, window.ibexa);
