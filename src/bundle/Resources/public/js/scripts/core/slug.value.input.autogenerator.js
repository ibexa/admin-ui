(function (global, ibexa) {
    class SlugValueInputAutogenerator {
        constructor(config = {}) {
            this.sourceInput = config.sourceInput;
            this.targetInput = config.targetInput;
            this.whitespaceTextReplacer = config.whitespaceTextReplacer || '_';
            this.shouldAutogenerateValue = config.shouldAutogenerateValue || !this.targetInput.value;
        }

        slugify(text) {
            const lowercaseText = text.toLowerCase();
            const normalizedText = lowercaseText.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            // workaround, as polish letter 'ł' doesn't belong to Unicode Block “Combining Diacritical Marks”
            const normalizedTextExtraChars = normalizedText.replace('ł', 'l');
            const noWhitespaceText = normalizedTextExtraChars.trim().replace(/ /g, this.whitespaceTextReplacer);
            const noSpecialCharsText = noWhitespaceText.replace(/[^a-zA-Z0-9_]/g, '');
            const noMultiHyphenText = noSpecialCharsText.replace(/_+/g, this.whitespaceTextReplacer);

            return noMultiHyphenText;
        }

        setTargetValue(value) {
            if (!this.shouldAutogenerateValue) {
                return;
            }

            const slugValue = this.slugify(value);

            this.targetInput.value = slugValue;
            this.targetInput.dispatchEvent(new Event('blur'));
        }

        attachEventsToTargetInput() {
            this.targetInput.addEventListener('keyup', ({ currentTarget }) => {
                this.shouldAutogenerateValue = currentTarget.value === '';
            });

            this.targetInput.addEventListener('input', ({ currentTarget }) => {
                this.shouldAutogenerateValue = currentTarget.value === '';
            });
        }

        attachEventsToSourceInput() {
            this.sourceInput.addEventListener('keyup', ({ currentTarget }) => {
                this.setTargetValue(currentTarget.value);
            });
        }

        init() {
            this.attachEventsToTargetInput();
            this.attachEventsToSourceInput();
        }
    }

    ibexa.addConfig('core.SlugValueInputAutogenerator', SlugValueInputAutogenerator);
})(window, window.ibexa);
