(function (global, doc, ibexa) {
    const SELECTOR_VISIBILITY_CHECKBOXES = '#ibexa-tab-location-view-locations .ibexa-content-locations__visibility-checkbox';
    const SELECTOR_VISIBILITY_FORM = 'form[name="location_update_visibility_data"]';
    const form = doc.querySelector(SELECTOR_VISIBILITY_FORM);
    const visibilityCheckboxes = doc.querySelectorAll(SELECTOR_VISIBILITY_CHECKBOXES);
    const refreshContentTree = () => {
        doc.body.dispatchEvent(new CustomEvent('ibexa-content-tree-refresh'));
    };
    const onVisibilityUpdated = ({ target }) => {
        const toggleLabel = target
            .closest('.ibexa-content-locations__visibility-toggler')
            .querySelector('.ibexa-content-locations__visibility-toggler-label');

        toggleLabel.classList.toggle('ibexa-content-locations__visibility-toggler-label--hidden');
    };
    const handleUpdateError = ibexa.helpers.notification.showErrorNotification;
    const handleUpdateSuccess = (event, { message }) => {
        onVisibilityUpdated(event);
        ibexa.helpers.notification.showSuccessNotification(message);
        refreshContentTree();
    };
    const handleUpdateResponse = (response) => {
        if (response.status !== 200) {
            throw new Error(response.statusText);
        }

        return response.json();
    };
    const updateVisibility = (event) => {
        form.querySelector('#location_update_visibility_data_location').value = event.target.value;
        form.querySelector('#location_update_visibility_data_hidden').checked = !event.target.checked;

        const request = new Request(form.action, {
            method: 'POST',
            body: new FormData(form),
            mode: 'same-origin',
            credentials: 'same-origin',
        });

        fetch(request).then(handleUpdateResponse).then(handleUpdateSuccess.bind(null, event)).catch(handleUpdateError);
    };

    visibilityCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', updateVisibility, false);
    });
})(window, window.document, window.ibexa);
