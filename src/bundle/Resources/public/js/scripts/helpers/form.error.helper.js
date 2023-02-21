(function (global, doc, ibexa) {
    const formatLine = (errorMessage) => {
        const errorIcon = `<svg class="ibexa-icon ibexa-icon--small ibexa-form-error__icon">
            <use xlink:href="${window.ibexa.helpers.icon.getIconPath('warning-triangle')}"></use>
        </svg>`;
        const container = document.createElement('em');
        const errorMessageNode = document.createTextNode(errorMessage);

        container.classList.add('ibexa-form-error__row');
        container.insertAdjacentHTML('beforeend', errorIcon);
        container.append(errorMessageNode);

        return container;
    };

    ibexa.addConfig('helpers.formError', {
        formatLine,
    });
})(window, window.document, window.ibexa);
