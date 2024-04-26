import initValidator from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/fieldType/validator/richtext-validator';

(function (global, doc, ibexa, React, ReactDOM) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezrichtext';
    const SELECTOR_INPUT = '.ibexa-data-source__richtext';
    const SELECTOR_LABEL = '.ibexa-field-edit__label';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';
    const selectContent = (config) => {
        const udwContainer = document.querySelector('#react-udw');
        const udwRoot = ReactDOM.createRoot(udwContainer);
        const confirmHandler = (items) => {
            if (typeof config.onConfirm === 'function') {
                config.onConfirm(items);
            }

            udwRoot.unmount();
        };
        const cancelHandler = () => {
            if (typeof config.onCancel === 'function') {
                config.onCancel();
            }

            udwRoot.unmount();
        };
        const mergedConfig = { ...config, onConfirm: confirmHandler, onCancel: cancelHandler };

        udwRoot.render(React.createElement(ibexa.modules.UniversalDiscovery, mergedConfig));
    };

    ibexa.addConfig('richText.alloyEditor.callbacks.selectContent', selectContent);

    doc.querySelectorAll(`${SELECTOR_FIELD} ${SELECTOR_INPUT}`).forEach((container) => {
        const richtextEditor = new ibexa.BaseRichText();

        richtextEditor.init(container);

        const validator = initValidator(container, SELECTOR_FIELD, SELECTOR_ERROR_NODE, SELECTOR_INPUT, SELECTOR_LABEL, richtextEditor);

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa, window.React, window.ReactDOM);
