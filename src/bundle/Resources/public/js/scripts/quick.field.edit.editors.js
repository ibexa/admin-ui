(function (global, doc, ibexa) {
    const CLASS_LEGACY_INPUT = 'ibexa-input';

    /**
     * Reads one validator's constraint object out of the field's validators map.
     *
     * @function getValidatorConstraints
     * @param {Object} validators
     * @param {String} validatorName
     * @returns {Object}
     */
    const getValidatorConstraints = (validators, validatorName) => validators?.[validatorName] || {};

    /**
     * Sets an attribute only when the bound is an actual number,
     * so a `null`/`false`/absent validator entry never emits a bogus attribute.
     *
     * @function setBoundAttribute
     * @param {HTMLElement} element
     * @param {String} attributeName
     * @param {*} bound
     * @returns {void}
     */
    const setBoundAttribute = (element, attributeName, bound) => {
        if (typeof bound === 'number') {
            element.setAttribute(attributeName, bound);
        }
    };

    /**
     * Builds the error message for one of the reused `ibexa.errors` translations.
     * Every one of those strings begins with a `{fieldName}` token; this
     * substitutes it with the field's display name (stashed on the element by
     * render() via `dataset.fieldName`, straight out of `fieldConfig.fieldName`)
     * using a replacer function rather than a replacement string, so a field
     * name containing `$&`/`$$` is inserted literally instead of being read as
     * a special replacement pattern. `fieldType/ibexa_integer.js` uses the
     * plain-string form of this same substitution and predates this feature -
     * it is left as-is. Falls back to dropping the `{fieldName} ` prefix —
     * never a literal token or a stray leading space — when the name is
     * missing or empty.
     *
     * @function formatErrorMessage
     * @param {String} template
     * @param {String} [fieldName]
     * @param {Object} [tokens]
     * @returns {String}
     */
    const formatErrorMessage = (template, fieldName, tokens = {}) => {
        let message = fieldName ? template.replace('{fieldName}', () => fieldName) : template.replace('{fieldName} ', '');

        Object.entries(tokens).forEach(([token, value]) => {
            message = message.replace(`{${token}}`, value);
        });

        return message;
    };

    const pad = (number) => String(number).padStart(2, '0');

    const createTextLikeInput = (type) => {
        const input = doc.createElement('input');

        input.type = type;
        input.className = `ids-input ids-input--${type} ids-input--medium`;

        return input;
    };

    const applyStringLengthConstraints = (input, validators) => {
        const { minStringLength, maxStringLength } = getValidatorConstraints(validators, 'StringLengthValidator');

        setBoundAttribute(input, 'minlength', minStringLength);
        setBoundAttribute(input, 'maxlength', maxStringLength);
    };

    const validateStringLength = (input) => {
        const { minLength, maxLength, value } = input;
        const { fieldName } = input.dataset;

        if (minLength >= 0 && value.length < minLength) {
            return formatErrorMessage(ibexa.errors.tooShort, fieldName, { minLength });
        }

        if (maxLength >= 0 && value.length > maxLength) {
            return formatErrorMessage(ibexa.errors.tooLong, fieldName, { maxLength });
        }

        return null;
    };

    const applyNumericRangeConstraints = (input, validators, validatorName, minKey, maxKey) => {
        const constraints = getValidatorConstraints(validators, validatorName);

        setBoundAttribute(input, 'min', constraints[minKey]);
        setBoundAttribute(input, 'max', constraints[maxKey]);
    };

    const validateNumericRange = (input, { parse, notANumberError }) => {
        if (input.value === '') {
            return null;
        }

        const { fieldName } = input.dataset;
        const value = parse(input.value);

        if (Number.isNaN(value)) {
            return formatErrorMessage(ibexa.errors[notANumberError], fieldName);
        }

        if (input.min !== '' && value < parse(input.min)) {
            return formatErrorMessage(ibexa.errors.isLess, fieldName, { minValue: input.min });
        }

        if (input.max !== '' && value > parse(input.max)) {
            return formatErrorMessage(ibexa.errors.isGreater, fieldName, { maxValue: input.max });
        }

        return null;
    };

    const editors = {
        ibexa_string: {
            render: (fieldValueHash, fieldConfig) => {
                const input = createTextLikeInput('text');

                input.value = fieldValueHash ?? '';
                input.dataset.fieldName = fieldConfig.fieldName ?? '';
                applyStringLengthConstraints(input, fieldConfig.validators);

                return input;
            },
            harvest: (input) => input.value,
            validate: (input) => validateStringLength(input),
        },
        ibexa_email: {
            render: (fieldValueHash) => {
                const input = createTextLikeInput('email');

                input.value = fieldValueHash ?? '';

                return input;
            },
            harvest: (input) => input.value,
        },
        ibexa_text: {
            render: (fieldValueHash) => {
                const textarea = doc.createElement('textarea');

                textarea.className = `${CLASS_LEGACY_INPUT} form-control ${CLASS_LEGACY_INPUT}--textarea`;
                textarea.value = fieldValueHash ?? '';

                return textarea;
            },
            harvest: (textarea) => textarea.value,
            // A plain Enter inserts a newline in a textarea instead of meaning "save" (only
            // Ctrl/Cmd+Enter does, per the editor row's own keydown handler), so this is the one
            // editor that cannot rely on the framework's Enter-to-save affordance and needs its
            // own explicit Save/Discard controls.
            needsExplicitActions: true,
        },
        ibexa_integer: {
            render: (fieldValueHash, fieldConfig) => {
                const input = createTextLikeInput('number');

                input.step = '1';
                input.value = typeof fieldValueHash === 'number' ? fieldValueHash : '';
                input.dataset.fieldName = fieldConfig.fieldName ?? '';
                applyNumericRangeConstraints(input, fieldConfig.validators, 'IntegerValueValidator', 'minIntegerValue', 'maxIntegerValue');

                return input;
            },
            harvest: (input) => (input.value === '' ? null : Number.parseInt(input.value, 10)),
            validate: (input) =>
                validateNumericRange(input, {
                    parse: (value) => Number.parseInt(value, 10),
                    notANumberError: 'isNotInteger',
                }),
        },
        ibexa_float: {
            render: (fieldValueHash, fieldConfig) => {
                const input = createTextLikeInput('number');

                input.step = 'any';
                input.value = typeof fieldValueHash === 'number' ? fieldValueHash : '';
                input.dataset.fieldName = fieldConfig.fieldName ?? '';
                applyNumericRangeConstraints(input, fieldConfig.validators, 'FloatValueValidator', 'minFloatValue', 'maxFloatValue');

                return input;
            },
            harvest: (input) => (input.value === '' ? null : Number.parseFloat(input.value)),
            validate: (input) =>
                validateNumericRange(input, {
                    parse: (value) => Number.parseFloat(value),
                    notANumberError: 'isNotFloat',
                }),
        },
        ibexa_boolean: {
            render: (fieldValueHash) => {
                const input = doc.createElement('input');

                input.type = 'checkbox';
                input.className = 'ids-input ids-input--checkbox form-check-input';
                input.checked = !!fieldValueHash;

                return input;
            },
            // Checkbox\Type::fromHash() has no null guard — never harvest anything but a boolean.
            harvest: (input) => input.checked,
        },
        ibexa_date: {
            render: (fieldValueHash) => {
                const input = doc.createElement('input');

                input.type = 'date';
                input.className = `${CLASS_LEGACY_INPUT} form-control ${CLASS_LEGACY_INPUT}--date`;

                if (fieldValueHash && typeof fieldValueHash.timestamp === 'number') {
                    const date = new Date(fieldValueHash.timestamp * 1000);

                    input.value = `${date.getUTCFullYear()}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())}`;
                }

                return input;
            },
            // UTC in both directions - keeps the round trip byte-stable and stops
            // the displayed date shifting depending on the viewer's timezone.
            harvest: (input) => {
                if (!input.value) {
                    return null;
                }

                const [year, month, day] = input.value.split('-').map(Number);

                return { timestamp: Date.UTC(year, month - 1, day) / 1000 };
            },
        },
        ibexa_datetime: {
            render: (fieldValueHash) => {
                const input = doc.createElement('input');

                input.type = 'datetime-local';
                input.step = '1';
                input.className = `${CLASS_LEGACY_INPUT} form-control ${CLASS_LEGACY_INPUT}--datetime-local`;

                if (fieldValueHash && typeof fieldValueHash.timestamp === 'number') {
                    const date = new Date(fieldValueHash.timestamp * 1000);
                    const datePart = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
                    const timePart = `${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;

                    input.value = `${datePart}T${timePart}`;
                }

                return input;
            },
            // Browser-local in both directions, unlike ibexa_date.
            harvest: (input) => (input.value ? { timestamp: Math.floor(new Date(input.value).getTime() / 1000) } : null),
        },
        ibexa_time: {
            render: (fieldValueHash) => {
                const input = doc.createElement('input');

                input.type = 'time';
                input.step = '1';
                input.className = `${CLASS_LEGACY_INPUT} form-control ${CLASS_LEGACY_INPUT}--time`;

                // 0 (midnight) is a legitimate value - never treat it as empty.
                if (typeof fieldValueHash === 'number') {
                    const hours = Math.floor(fieldValueHash / 3600);
                    const minutes = Math.floor((fieldValueHash % 3600) / 60);
                    const seconds = fieldValueHash % 60;

                    input.value = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                }

                return input;
            },
            harvest: (input) => {
                if (!input.value) {
                    return null;
                }

                const [hours, minutes, seconds = 0] = input.value.split(':').map(Number);

                return hours * 3600 + minutes * 60 + seconds;
            },
        },
    };

    ibexa.addConfig('quickEdit.editors', editors, true);
})(window, window.document, window.ibexa);
