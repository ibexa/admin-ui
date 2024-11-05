(function (doc, ibexa) {
    const sourceInputs = doc.querySelectorAll('[data-autogenerate-identifier-target-selector]');

    const generateIdentifier = (elements, shouldAutogenerateValue = false) => {
        elements.forEach((sourceInput) => {
            const { autogenerateIdentifierTargetSelector } = sourceInput.dataset;
            const targetInput = doc.querySelector(autogenerateIdentifierTargetSelector);
            const identifierAutogenerator = new ibexa.core.SlugValueInputAutogenerator({
                sourceInput,
                targetInput,
                shouldAutogenerateValue: shouldAutogenerateValue,
            });
            identifierAutogenerator.init();
        });
    };

    const attachListeners = () => {
        doc.body.addEventListener(
            'ibexa-recall-autogenerate-identifier',
            (event) => {
                const { fieldNode, shouldAutogenerateValue } = event.detail;
                const sourceInputs = fieldNode.querySelectorAll('[data-autogenerate-identifier-target-selector]');

                generateIdentifier(sourceInputs, shouldAutogenerateValue);
            },
            false,
        );
    };

    generateIdentifier(sourceInputs);
    attachListeners();
})(document, window.ibexa);
