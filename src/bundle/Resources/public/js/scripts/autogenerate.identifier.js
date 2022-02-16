(function(global, doc) {
    const sourceInputs = doc.querySelectorAll('[data-autogenerate-identifier-target-selector]');
    const slugify = (text) => {
        const lowercaseText = text.toLowerCase();
        const normalizedText = lowercaseText.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        // workaround, as polish letter 'ł' doesn't belong to Unicode Block “Combining Diacritical Marks”
        const normalizedTextExtraChars = normalizedText.replace('ł', 'l');
        const noWhitespaceText = normalizedTextExtraChars.trim().replace(/ /g, '_');
        const noSpecialCharsText = noWhitespaceText.replace(/[^a-zA-Z0-9_]/g, '');
        const noMultiHyphenText = noSpecialCharsText.replace(/_+/g, '_');

        return noMultiHyphenText;
    };

    sourceInputs.forEach((sourceInput) => {
        const { autogenerateIdentifierTargetSelector } = sourceInput.dataset;
        const targetInput = doc.querySelector(autogenerateIdentifierTargetSelector);
        let shouldAutogenerateIdentifier = !targetInput.value;

        targetInput.addEventListener('keyup', (event) => {
            shouldAutogenerateIdentifier = event.currentTarget.value === '';
        });

        targetInput.addEventListener('input', (event) => {
            shouldAutogenerateIdentifier = event.currentTarget.value === '';
        });

        sourceInput.addEventListener('keyup', (event) => {
            if (shouldAutogenerateIdentifier) {
                const slugValue = slugify(event.currentTarget.value);

                targetInput.value = slugValue;
                targetInput.dispatchEvent(new Event('blur'));
            }
        });
    });
})(window, document);
