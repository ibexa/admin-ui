(function (doc, ibexa) {
    const sourceInputs = doc.querySelectorAll('[data-autogenerate-identifier-target-selector]');

    const initAutogenerator = (elements, shouldAutogenerateValue) => {
        elements.forEach((sourceInput) => {
            const { autogenerateIdentifierTargetSelector } = sourceInput.dataset;
            const targetInput = doc.querySelector(autogenerateIdentifierTargetSelector);
            const identifierAutogenerator = new ibexa.core.SlugValueInputAutogenerator({
                sourceInput,
                targetInput,
                shouldAutogenerateValue: shouldAutogenerateValue || !targetInput.value,
            });

            identifierAutogenerator.init();
        });
    };
    const attachListeners = () => {
        doc.body.addEventListener(
            'ibexa-autogenerate-identifier:init',
            (event) => {
                const { fieldNode, shouldAutogenerateValue } = event.detail;
                const sourceFields = fieldNode.querySelectorAll('[data-autogenerate-identifier-target-selector]');

                initAutogenerator(sourceFields, shouldAutogenerateValue);
            },
            false,
        );
    };

    initAutogenerator(sourceInputs);
    attachListeners();
})(document, window.ibexa);
