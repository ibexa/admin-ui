(function (global, doc, ibexa) {
    const containers = doc.querySelectorAll('.ibexa-adaptive-filters');

    containers.forEach((container) => {
        const clearBtn = container.querySelector('.ibexa-adaptive-filters__clear-btn');
        const applyBtn = container.querySelector('.ibexa-adaptive-filters__submit-btn');
        const dropdownNodes = [...container.querySelectorAll('.ibexa-dropdown')];
        const textInputNodes = [...container.querySelectorAll('.ibexa-input--text')];
        const dateInputNodes = [...container.querySelectorAll('.ibexa-input--date')];
        const originalValuesMap = new Map();
        const dropdownSelectionsEqual = (selection1, selection2) => {
            if (selection1.length !== selection2.length) {
                return false;
            }

            for (let i = 0; i < selection1.length; ++i) {
                if (selection1[i] !== selection2[i]) return false;
            }

            return true;
        };
        const checkFieldsValuesChanged = () => {
            return (
                dropdownNodes.some((dropdownNode) => {
                    const dropdown = dropdownNode.ibexaInstance;
                    const value = [...dropdown.getSelectedItems()].map((item) => item.value);
                    const originalValue = originalValuesMap.get(dropdown);

                    return !dropdownSelectionsEqual(value, originalValue);
                }) ||
                textInputNodes.some((textInputNode) => {
                    const { value } = textInputNode;
                    const originalValue = originalValuesMap.get(textInputNode);

                    return value !== originalValue;
                })
            );
        };
        const checkAreFiltersCleared = () => {
            return (
                textInputNodes.every((textInputNode) => textInputNode.disabled || textInputNode.value === '') &&
                dropdownNodes.every((dropdownNode) => {
                    const isDisabled = dropdownNode.classList.contains('ibexa-dropdown--disabled');
                    const selectNode = dropdownNode.querySelector('.ibexa-input--select');
                    const dropdown = dropdownNode.ibexaInstance;

                    return isDisabled || (dropdown.canSelectOnlyOne ? selectNode.selectedIndex === 0 : selectNode.selectedIndex === -1);
                })
            );
        };
        const clearForm = () => {
            textInputNodes.forEach((textInputNode) => {
                if (!textInputNode.disabled) {
                    textInputNode.value = '';
                }
            });
            dateInputNodes.forEach((dateInputNode) => {
                if (!dateInputNode.disabled) {
                    const datePickerNode = dateInputNode.closest('.ibexa-picker');
                    if (datePickerNode) {
                        const datePickerInstance = ibexa.helpers.objectInstances.getInstance(datePickerNode);

                        datePickerInstance.clear();
                    }

                    const dateTimeRangeSingleNode = dateInputNode.closest('.ibexa-date-time-range-single');

                    if (dateTimeRangeSingleNode) {
                        const dateTimeRangeSingleInstance = ibexa.helpers.objectInstances.getInstance(dateTimeRangeSingleNode);

                        dateTimeRangeSingleInstance.clearDates();
                    }
                }
            });
            dropdownNodes.forEach((dropdownNode) => {
                const isDisabled = dropdownNode.classList.contains('ibexa-dropdown--disabled');

                if (!isDisabled) {
                    const dropdown = dropdownNode.ibexaInstance;

                    if (dropdown.canSelectOnlyOne) {
                        dropdown.selectFirstOption();
                    } else {
                        dropdown.clearCurrentSelection();
                    }
                }
            });
        };
        const handleFormClear = () => {
            clearForm();

            if (clearBtn) {
                clearBtn.disabled = true;
            }

            if (applyBtn) {
                applyBtn.disabled = !checkFieldsValuesChanged();
                applyBtn.click();
            }
        };
        const handleInputChange = () => {
            if (clearBtn) {
                clearBtn.disabled = checkAreFiltersCleared();
            }

            if (applyBtn) {
                applyBtn.disabled = !checkFieldsValuesChanged();
            }
        };

        dropdownNodes.forEach((dropdownNode) => {
            const dropdown = dropdownNode.ibexaInstance;
            const originalValue = [...dropdown.getSelectedItems()].map((item) => item.value);

            originalValuesMap.set(dropdown, originalValue);
        });
        textInputNodes.forEach((textInputNode) => {
            const originalValue = textInputNode.value;

            originalValuesMap.set(textInputNode, originalValue);
        });

        if (applyBtn) {
            applyBtn.disabled = true;
        }

        if (clearBtn) {
            clearBtn.disabled = checkAreFiltersCleared();
            clearBtn.addEventListener('click', handleFormClear, false);
        }

        dropdownNodes.forEach((dropdownNode) => {
            const select = dropdownNode.querySelector('.ibexa-input--select');

            select.addEventListener('change', handleInputChange, false);
        });
        textInputNodes.forEach((textInputNode) => textInputNode.addEventListener('input', handleInputChange, false));
    });
})(window, window.document, window.ibexa);
