(function (doc, ibexa) {
    const sourceInputs = doc.querySelectorAll('[data-autogenerate-identifier-target-selector]');

    const initAutogenerator = (elements, shouldAutogenerateValue = false) => {
        elements.forEach((sourceInput) => {
            const { autogenerateIdentifierTargetSelector } = sourceInput.dataset;
            const targetInput = doc.querySelector(autogenerateIdentifierTargetSelector);
            const identifierAutogenerator = new ibexa.core.SlugValueInputAutogenerator({
                sourceInput,
                targetInput,
                shouldAutogenerateValue,
            });

            identifierAutogenerator.init();
        });
    };

    const attachListeners = () => {
        doc.body.addEventListener(
            'ibexa-autogenerate-identifier:init',
            (event) => {
                const { fieldNode, shouldAutogenerateValue } = event.detail;
                const sourceInputs = fieldNode.querySelectorAll('[data-autogenerate-identifier-target-selector]');

                initAutogenerator(sourceInputs, shouldAutogenerateValue);
            },
            false,
        );
    };

    initAutogenerator(sourceInputs);
    attachListeners();
})(document, window.ibexa);
