import * as middleEllipsisHelper from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/middle.ellipsis';

(function (global, doc, ibexa, React, ReactDOMClient) {
    const SELECTOR_RESET_STARTING_LOCATION_BTN = '.ibexa-tag__remove-btn';
    const defaultLocationContainers = doc.querySelectorAll('.ibexa-default-location');
    const udwContainer = doc.getElementById('react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const renderTagItem = (container, [item]) => {
        const template = container.dataset.template.replaceAll('{{ content }}', item.name);

        container.innerHTML = template;

        const deleteBtn = container.querySelector(SELECTOR_RESET_STARTING_LOCATION_BTN);

        middleEllipsisHelper.parse();

        deleteBtn.addEventListener('click', resetStartingLocation, false);
    };
    const onConfirm = (btn, items) => {
        closeUDW();

        const locationId = items[0].id;
        const container = btn.closest('.ibexa-default-location');
        const pathSelector = container.querySelector('.ibexa-default-location__path-selector');

        container.querySelector(btn.dataset.relationRootInputSelector).value = locationId;

        pathSelector.classList.add('ibexa-default-location__path-selector--filled');

        ibexa.helpers.tagViewSelect.buildItemsFromUDWResponse(
            items,
            (item) => item.pathString,
            renderTagItem.bind(null, container.querySelector('.ibexa-default-location__selected-path')),
        );
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);

        udwRoot = ReactDOMClient.createRoot(udwContainer);
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
    const resetStartingLocation = ({ currentTarget }) => {
        const container = currentTarget.closest('.ibexa-default-location');
        const udwBtn = container.querySelector('.ibexa-btn--udw-relation-default-location');
        const pathSelector = container.querySelector('.ibexa-default-location__path-selector');
        const { relationRootInputSelector } = udwBtn.dataset;

        container.querySelector(relationRootInputSelector).value = '';
        container.querySelector('.ibexa-default-location__selected-path').innerHTML = '';
        pathSelector.classList.remove('ibexa-default-location__path-selector--filled');
    };
    const attachEvents = (container) => {
        const udwBtn = container.querySelector('.ibexa-btn--udw-relation-default-location');
        const deleteBtn = container.querySelector(SELECTOR_RESET_STARTING_LOCATION_BTN);
        const choices = container.querySelectorAll('input[type="radio"]');

        udwBtn.addEventListener('click', openUDW, false);
        deleteBtn?.addEventListener('click', resetStartingLocation, false);
        choices.forEach((choice) => choice.addEventListener('change', toggleDisabledState.bind(null, container), false));
    };
    const toggleDisabledState = (container) => {
        const locationBtn = container.querySelector('.ibexa-btn--udw-relation-default-location');
        const deleteBtn = container.querySelector(SELECTOR_RESET_STARTING_LOCATION_BTN);
        const isDisabled = !container.querySelector('input[value="1"]').checked;

        locationBtn.classList.toggle('disabled', isDisabled);
        deleteBtn?.classList.toggle('disabled', isDisabled);
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
        middleEllipsisHelper.parse();
    });
})(window, window.document, window.ibexa, window.React, window.ReactDOMClient);
