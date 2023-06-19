(function (global, doc, ibexa) {
    const backdrop = doc.createElement('div');
    const bodyFirstNode = document.body.firstChild;

    backdrop.classList.add('ibexa-backdrop');
    doc.body.insertBefore(backdrop, bodyFirstNode);

    const toggleBackdrop = (shouldBackdropDisplay, config = {}) => {
        if (shouldBackdropDisplay) {
            const { isTransparent, extraClasses } = config;
            const classes = {
                'ibexa-backdrop--transparent': isTransparent,
            };
            const backdropClasses = Object.keys(classes).filter((property) => classes[property]);
            const backdropExtraClasses = Array.isArray(extraClasses) ? extraClasses : [extraClasses];

            backdrop.classList.add(...backdropClasses, ...backdropExtraClasses);
        } else {
            backdrop.className = 'ibexa-backdrop';
        }

        backdrop.classList.toggle('ibexa-backdrop--active', shouldBackdropDisplay);

        if (shouldBackdropDisplay) {
            document.dispatchEvent(new CustomEvent('ibexa-backdrop:after-show'));
        }
    };

    const showBackdrop = (config) => {
        toggleBackdrop(true, config);
    };

    const hideBackdrop = () => {
        toggleBackdrop(false);
    };

    const getBackdrop = () => {
        return backdrop;
    };

    ibexa.addConfig('helpers.backdrop', {
        show: showBackdrop,
        hide: hideBackdrop,
        get: getBackdrop,
    });
})(window, window.document, window.ibexa);
