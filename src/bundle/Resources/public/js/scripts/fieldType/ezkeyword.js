import { parse as parseTooltips } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezkeyword';
    const SELECTOR_TAGGIFY = '.ibexa-data-source__taggify';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';
    const CLASS_TAGGIFY_FOCUS = 'ibexa-data-source__taggify--focused';
    const ENTER_KEY_CODE = 13;
    const COMMA_KEY_CODE = 188;
    const tagsObserverConfig = {
        childList: true,
        subtree: true,
    };
    const addTooltipToTag = (mutationList) => {
        mutationList.forEach((mutation) => {
            if (mutation.target.classList.contains('taggify__tags')) {
                mutation.addedNodes.forEach((addedNode) => {
                    const labelElement = addedNode.querySelector('.taggify__tag-label');

                    labelElement.title = labelElement.innerText;
                });

                parseTooltips(mutation.target);
            }
        });
    };

    class IbexaKeywordValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the keywords input
         *
         * @method validateKeywords
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaKeywordValidator
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
        const taggifyContainer = field.querySelector(SELECTOR_TAGGIFY);
        const tagsObserver = new MutationObserver(addTooltipToTag);

        tagsObserver.observe(taggifyContainer, tagsObserverConfig);

        const validator = new IbexaKeywordValidator({
            classInvalid: 'is-invalid',
            fieldSelector: SELECTOR_FIELD,
            eventsMap: [
                {
                    isValueValidator: false,
                    selector: `${SELECTOR_FIELD} .taggify__input`,
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
        const taggify = new global.Taggify({
            containerNode: taggifyContainer,
            displayLabel: false,
            displayInputValues: false,
            hotKeys: [ENTER_KEY_CODE, COMMA_KEY_CODE],
        });
        const keywordInput = field.querySelector('.ibexa-data-source__input-wrapper .ibexa-data-source__input.form-control');
        const updateKeywords = updateValue.bind(this, keywordInput);
        const addFocusState = () => taggifyContainer.classList.add(CLASS_TAGGIFY_FOCUS);
        const removeFocusState = () => taggifyContainer.classList.remove(CLASS_TAGGIFY_FOCUS);
        const taggifyInput = taggifyContainer.querySelector('.taggify__input');

        if (keywordInput.required) {
            taggifyInput.setAttribute('required', true);
        }

        validator.init();

        if (keywordInput.value.length) {
            taggify.updateTags(
                keywordInput.value.split(',').map((item) => ({
                    id: Math.floor((1 + Math.random()) * 0x10000).toString(16),
                    label: item,
                })),
            );
        }

        taggifyContainer.addEventListener('tagsCreated', updateKeywords, false);
        taggifyContainer.addEventListener('tagRemoved', updateKeywords, false);
        taggifyInput.addEventListener('focus', addFocusState, false);
        taggifyInput.addEventListener('blur', removeFocusState, false);

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa);
