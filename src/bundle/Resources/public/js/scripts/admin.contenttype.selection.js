(function(global, doc) {
    const SELECTOR_TEMPLATE = '.ibexaselection-settings-option-value-prototype';
    const SELECTOR_OPTION = '.ibexaselection-settings-option-value';
    const SELECTOR_OPTIONS_LIST = '.ibexaselection-settings-option-list';
    const SELECTOR_BTN_REMOVE = '.ibexaselection-settings-option-remove';
    const SELECTOR_BTN_ADD = '.ibexaselection-settings-option-add';
    const NUMBER_PLACEHOLDER = /__number__/g;

    doc.querySelectorAll('.ibexaselection-settings.options').forEach((container) => {
        const findCheckedOptions = () => container.querySelectorAll('.ibexaselection-settings-option-checkbox:checked');
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
    });
})(window, window.document);
