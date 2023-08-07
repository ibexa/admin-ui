(function (global, doc, ibexa) {
    // @deprecated, will be removed in 5.0
    ibexa.addConfig('helpers.formError', {
        formatLine: (...args) => {
            console.warn(
                'helpers.formError.formatLine method is deprecated and will be removed in 5.0, please use helpers.formValidation.formatErrorLine instead.',
            );

            return ibexa.helpers.formValidation.formatErrorLine(...args);
        },
    });
})(window, window.document, window.ibexa);
