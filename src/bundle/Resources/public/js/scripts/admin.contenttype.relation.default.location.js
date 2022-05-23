(function (global, doc, ibexa, React, ReactDOM) {
    const SELECTOR_RESET_STARTING_LOCATION_BTN = '.ibexa-btn--reset-starting-location';
    const defaultLocationContainers = doc.querySelectorAll('.ibexa-default-location');
    const udwContainer = doc.getElementById('react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const onConfirm = (btn, items) => {
        closeUDW();

        const locationId = items[0].id;
        const locationName = items[0].ContentInfo.Content.TranslatedName;
        const objectRelationListSettingsWrapper = btn.closest('.ezobjectrelationlist-settings');
        const objectRelationSettingsWrapper = btn.closest('.ezobjectrelation-settings');

        toggleResetStartingLocationBtn(btn.parentNode.querySelector(SELECTOR_RESET_STARTING_LOCATION_BTN), true);

        if (objectRelationListSettingsWrapper) {
            objectRelationListSettingsWrapper.querySelector(btn.dataset.relationRootInputSelector).value = locationId;
            objectRelationListSettingsWrapper.querySelector(btn.dataset.relationSelectedRootNameSelector).innerHTML = locationName;
        } else {
            objectRelationSettingsWrapper.querySelector(btn.dataset.relationRootInputSelector).value = locationId;
            objectRelationSettingsWrapper.querySelector(btn.dataset.relationSelectedRootNameSelector).innerHTML = locationName;
        }
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);

        udwRoot = ReactDOM.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: onConfirm.bind(null, event.currentTarget),
                onCancel,
                title: event.currentTarget.dataset.universaldiscoveryTitle,
                multiple: false,
                ...config,
            }),
        );
    };
    const toggleResetStartingLocationBtn = (button, isEnabled) => {
        if (isEnabled) {
            button.removeAttribute('disabled');
        } else {
            button.setAttribute('disabled', true);
        }
    };
    const resetStartingLocation = (event) => {
        const button = event.currentTarget;
        const { relationRootInputSelector, relationSelectedRootNameSelector } = button.dataset;

        doc.querySelector(relationRootInputSelector).value = '';
        doc.querySelector(relationSelectedRootNameSelector).innerHTML = '';

        toggleResetStartingLocationBtn(button, false);
    };
    const attachEvents = (container) => {
        const udwBtn = container.querySelector('.ibexa-btn--udw-relation-default-location');
        const deleteBtn = container.querySelector(SELECTOR_RESET_STARTING_LOCATION_BTN);
        const choices = container.querySelectorAll('input[type="radio"]');

        udwBtn.addEventListener('click', openUDW, false);
        deleteBtn.addEventListener('click', resetStartingLocation, false);
        choices.forEach((choice) => choice.addEventListener('change', toggleDisabledState.bind(null, container), false));
    };
    const toggleDisabledState = (container) => {
        const locationBtn = container.querySelector('.ibexa-btn--udw-relation-default-location');
        const deleteBtn = container.querySelector(SELECTOR_RESET_STARTING_LOCATION_BTN);
        const isDisabled = !container.querySelector('input[value="1"]').checked;

        locationBtn.classList.toggle('disabled', isDisabled);
        toggleResetStartingLocationBtn(deleteBtn, !isDisabled);
    };

    doc.body.addEventListener(
        'ibexa-drop-field-definition',
        (event) => {
            const { nodes } = event.detail;

            nodes.forEach((node) => {
                const defaultLocationContainer = node.querySelector('.ibexa-default-location');

                if (!defaultLocationContainer) {
                    return;
                }

                attachEvents(defaultLocationContainer);
                toggleDisabledState(defaultLocationContainer);
            });
        },
        false,
    );

    defaultLocationContainers.forEach((defaultLocationContainer) => {
        attachEvents(defaultLocationContainer);
        toggleDisabledState(defaultLocationContainer);
    });
})(window, window.document, window.ibexa, window.React, window.ReactDOM);
