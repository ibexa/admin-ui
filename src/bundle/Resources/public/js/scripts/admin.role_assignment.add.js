(function (global, doc, ibexa, React, ReactDOM) {
    const udwContainer = doc.getElementById('react-udw');
    const limitationsRadio = doc.querySelectorAll('.ibexa-assign__limitations-item-radio');
    const selectSubtreeWidget = new ibexa.core.TagViewSelect({
        fieldContainer: doc.querySelector('.ibexa-assign__limitations-item-subtree'),
    });
    const selectUsersWidget = new ibexa.core.TagViewSelect({
        fieldContainer: doc.querySelector('.ibexa-assign__users'),
    });
    const selectGroupsWidget = new ibexa.core.TagViewSelect({
        fieldContainer: doc.querySelector('.ibexa-assign__groups'),
    });
    const selectSubtreeBtn = doc.querySelector('.ibexa-assign__limitations-item-select-subtree');
    const selectUsersBtn = doc.querySelector('#role_assignment_create_users__btn');
    const selectGroupsBtn = doc.querySelector('#role_assignment_create_groups__btn');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const confirmSubtreeUDW = (data) => {
        ibexa.helpers.tagViewSelect.buildItemsFromUDWResponse(
            data,
            (item) => item.id,
            (items) => {
                selectSubtreeWidget.addItems(items, true);

                closeUDW();
            },
        );
    };
    const openSubtreeUDW = (event) => {
        event.preventDefault();

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);
        const selectedLocations = selectSubtreeWidget.inputField.value;
        const selectedLocationsIds = selectedLocations ? selectedLocations.split(',') : [];

        udwRoot = ReactDOM.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: confirmSubtreeUDW.bind(this),
                onCancel: closeUDW,
                multiple: true,
                selectedLocations: selectedLocationsIds,
                ...config,
            }),
        );
    };
    const confirmUsersAndGroupsUDW = (widget, selectedItems) => {
        ibexa.helpers.tagViewSelect.buildItemsFromUDWResponse(
            selectedItems,
            (item) => item.ContentInfo.Content._id,
            (items) => {
                const itemsMap = selectedItems.reduce((output, item) => ({ ...output, [item.ContentInfo.Content._id]: item.id }), {});

                widget.addItems(items, true);
                widget.selectBtn.setAttribute('data-items-map', JSON.stringify(itemsMap));

                closeUDW();
            },
        );
    };
    const openUsersAndGroupsUDW = (widget, event) => {
        event.preventDefault();

        const selectBtn = event.currentTarget;
        const config = JSON.parse(selectBtn.dataset.udwConfig);
        const itemsMap = JSON.parse(selectBtn.dataset.itemsMap);
        const selectedContent = widget.inputField.value;
        const selectedContentIds = selectedContent ? selectedContent.split(',') : [];
        const selectedLocationsIds = selectedContentIds.map((contentId) => itemsMap[contentId]);

        udwRoot = ReactDOM.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: confirmUsersAndGroupsUDW.bind(this, widget),
                onCancel: () => udwRoot.unmount(),
                title: selectBtn.dataset.universaldiscoveryTitle,
                multiple: true,
                selectedLocations: selectedLocationsIds,
                ...config,
            }),
        );
    };
    const toggleDisabledState = () => {
        limitationsRadio.forEach((radio) => {
            const disableNode = radio.closest('.ibexa-assign__limitations-item').querySelector(radio.dataset.disableSelector);

            if (disableNode) {
                if (radio.dataset.disableClass) {
                    disableNode.classList.toggle(radio.dataset.disableClass, !radio.checked);
                } else {
                    disableNode.toggleAttribute('disabled', !radio.checked);
                }
            }
        });
    };

    selectSubtreeBtn.addEventListener('click', openSubtreeUDW, false);
    selectUsersBtn.addEventListener('click', openUsersAndGroupsUDW.bind(null, selectUsersWidget), false);
    selectGroupsBtn.addEventListener('click', openUsersAndGroupsUDW.bind(null, selectGroupsWidget), false);
    limitationsRadio.forEach((radio) => radio.addEventListener('change', toggleDisabledState, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOM);
