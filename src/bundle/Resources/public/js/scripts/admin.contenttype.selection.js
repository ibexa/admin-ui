(function (global, doc) {
    const SELECTOR_TEMPLATE = '.ibexa_selection-settings-option-value-prototype';
    const SELECTOR_OPTION = '.ibexa_selection-settings-option-value';
    const SELECTOR_OPTIONS_LIST = '.ibexa_selection-settings-option-list';
    const SELECTOR_BTN_REMOVE = '.ibexa_selection-settings-option-remove';
    const SELECTOR_BTN_ADD = '.ibexa_selection-settings-option-add';
    const NUMBER_PLACEHOLDER = /__number__/g;
    const initField = (container) => {
        const findCheckedOptions = () => container.querySelectorAll('.ibexa_selection-settings-option-checkbox:checked');
        const toggleDisableState = () => {
            const disabledState = !!findCheckedOptions().length;
            const methodName = disabledState ? 'removeAttribute' : 'setAttribute';

            container.querySelector(SELECTOR_BTN_REMOVE)[methodName]('disabled', disabledState);
        };
        const addOption = () => {
            const template = container.querySelector(SELECTOR_TEMPLATE).innerHTML;
            const optionsList = container.querySelector(SELECTOR_OPTIONS_LIST);
            const nextId = parseInt(optionsList.dataset.nextOptionId, 10);

            optionsList.dataset.nextOptionId = nextId + 1;
            optionsList.insertAdjacentHTML('beforeend', template.replace(NUMBER_PLACEHOLDER, nextId));
        };
        const removeOptions = () => {
            findCheckedOptions().forEach((element) => element.closest(SELECTOR_OPTION).remove());
            toggleDisableState();
        };

        container.querySelector(SELECTOR_OPTIONS_LIST).addEventListener('click', toggleDisableState, false);
        container.querySelector(SELECTOR_BTN_ADD).addEventListener('click', addOption, false);
        container.querySelector(SELECTOR_BTN_REMOVE).addEventListener('click', removeOptions, false);
    };

    doc.querySelectorAll('.ibexa_selection-settings.options').forEach(initField);
    doc.body.addEventListener(
        'ibexa-drop-field-definition',
        (event) => {
            const { nodes } = event.detail;

            nodes.forEach((node) => {
                const isSelectionFieldType = node.querySelector(SELECTOR_OPTIONS_LIST);

                if (isSelectionFieldType) {
                    initField(node);
                }
            });
        },
        false,
    );
})(window, window.document);
