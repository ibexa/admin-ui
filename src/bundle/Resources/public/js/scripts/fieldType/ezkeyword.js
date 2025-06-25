(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezkeyword';
    const SELECTOR_TAGGIFY_CONTAINER = '.ibexa-data-source__taggify';
    const SELECTOR_TAGGIFY = '.ibexa-data-source__taggify .ibexa-taggify';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';

    class EzKeywordValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the keywords input
         *
         * @method validateKeywords
         * @param {Event} event
         * @returns {Object}
         * @memberof EzKeywordValidator
         */
        validateKeywords(event) {
            const fieldContainer = event.currentTarget.closest(SELECTOR_FIELD);
            const input = fieldContainer.querySelector('.ibexa-data-source__input-wrapper .ibexa-data-source__input');
            const label = fieldContainer.querySelector('.ibexa-field-edit__label').innerHTML;
            const isRequired = input.required;
            const isEmpty = !input.value.trim().length;
            const isError = isEmpty && isRequired;
            const result = { isError };

            if (isError) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            }

            return result;
        }
    }

    /**
     * Updates input value with provided value
     *
     * @function updateValue
     * @param {HTMLElement} input
     * @param {Event} event
     */
    const updateValue = (input, event) => {
        input.value = event.detail.tags.map((tag) => tag.label).join();

        input.dispatchEvent(new Event('change'));
    };

    doc.querySelectorAll(SELECTOR_FIELD).forEach((field) => {
        const taggifyContainer = field.querySelector(SELECTOR_TAGGIFY_CONTAINER);
        const ibexaTaggifyNode = taggifyContainer.querySelector('.ibexa-taggify');

        const validator = new EzKeywordValidator({
            classInvalid: 'is-invalid',
            fieldSelector: SELECTOR_FIELD,
            eventsMap: [
                {
                    isValueValidator: false,
                    selector: `${SELECTOR_FIELD} .ibexa-taggify__input`,
                    eventName: 'blur',
                    callback: 'validateKeywords',
                    errorNodeSelectors: [SELECTOR_ERROR_NODE],
                    invalidStateSelectors: [SELECTOR_TAGGIFY],
                },
                {
                    selector: `${SELECTOR_FIELD} .ibexa-data-source__input.form-control`,
                    eventName: 'change',
                    callback: 'validateKeywords',
                    errorNodeSelectors: [SELECTOR_ERROR_NODE],
                    invalidStateSelectors: [SELECTOR_TAGGIFY],
                },
            ],
        });

        const keywordInput = field.querySelector('.ibexa-data-source__input-wrapper .ibexa-data-source__input.form-control');
        class EzKeywordTaggify extends ibexa.core.Taggify {
            afterTagsUpdate() {
                const tags = [...this.tags];
                const tagsInputValue = tags.join();

                if (keywordInput.value !== tagsInputValue) {
                    keywordInput.value = tags.join();
                    keywordInput.dispatchEvent(new Event('change'));
                }
            }
        }
        const taggify = new EzKeywordTaggify({
            container: ibexaTaggifyNode,
            acceptKeys: ['Enter', ','],
        });
        const updateKeywords = updateValue.bind(this, keywordInput);
        const taggifyInput = taggifyContainer.querySelector('.taggify__input');

        if (keywordInput.required) {
            taggifyInput.setAttribute('required', true);
        }

        validator.init();
        taggify.init();

        if (keywordInput.value.length) {
            keywordInput.value.split(',').forEach((tag) => {
                taggify.addTag(tag, tag);
            });
        }

        taggifyContainer.addEventListener('tagsCreated', updateKeywords, false);
        taggifyContainer.addEventListener('tagRemoved', updateKeywords, false);

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa);
