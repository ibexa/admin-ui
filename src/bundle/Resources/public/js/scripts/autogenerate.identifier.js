(function (doc, ibexa) {
    const sourceInputs = doc.querySelectorAll('[data-autogenerate-identifier-target-selector]');

    sourceInputs.forEach((sourceInput) => {
        const { autogenerateIdentifierTargetSelector } = sourceInput.dataset;
        const targetInput = doc.querySelector(autogenerateIdentifierTargetSelector);
        const identifierAutogenerator = new ibexa.core.SlugValueInputAutogenerator({
            sourceInput,
            targetInput,
        });

        identifierAutogenerator.init();
    });
})(document, window.ibexa);
