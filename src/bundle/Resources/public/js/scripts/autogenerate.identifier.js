(function (doc, ibexa) {
    const sourceInputs = doc.querySelectorAll('[data-autogenerate-identifier-target-selector]');

    sourceInputs.forEach((sourceInput) => {
        const { autogenerateIdentifierTargetSelector } = sourceInput.dataset;
        const targetInput = doc.querySelector(autogenerateIdentifierTargetSelector);
        const autogenerateIdentifier = new ibexa.core.AutogeneratorInputValue({
            sourceInput,
            targetInput,
        });

        autogenerateIdentifier.init();
    });
})(document, window.ibexa);
