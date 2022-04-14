(function (global, doc, ibexa, bootstrap, Routing) {
    const editActions = doc.querySelectorAll('.ibexa-extra-actions--edit, .ibexa-extra-actions--edit-user');
    const resetRadioButtons = (btns) =>
        btns.forEach((btn) => {
            btn.checked = false;
        });
    const addDraft = (form) => {
        form.submit();
        bootstrap.Modal.getOrCreateInstance(doc.querySelector('#version-draft-conflict-modal')).hide();
    };
    const redirectToUserEdit = (languageCode, contentId, form) => {
        const versionNo = form.querySelector('#user_edit_version_info_version_no').value;

        window.location.href = Routing.generate('ibexa.user.update', { contentId, versionNo, language: languageCode });
    };
    const onModalHidden = (btns) => {
        resetRadioButtons(btns);

        const event = new CustomEvent('ibexa-draft-conflict-modal-hidden');

        doc.body.dispatchEvent(event);
    };
    const attachModalListeners = (wrapper, form, btns) => {
        const addDraftButton = wrapper.querySelector('.ibexa-btn--add-draft');
        const conflictModal = doc.querySelector('#version-draft-conflict-modal');

        if (addDraftButton) {
            addDraftButton.addEventListener('click', addDraft.bind(null, form), false);
        }

        wrapper
            .querySelectorAll('.ibexa-btn--prevented')
            .forEach((btn) => btn.addEventListener('click', (event) => event.preventDefault(), false));

        if (conflictModal) {
            bootstrap.Modal.getOrCreateInstance(conflictModal).show();

            conflictModal.addEventListener('hidden.bs.modal', onModalHidden.bind(null, btns));
            conflictModal.addEventListener('shown.bs.modal', () => ibexa.helpers.tooltips.parse());
        }
    };
    const showModal = (form, btns, modalHtml) => {
        const wrapper = doc.querySelector('.ibexa-modal-wrapper');

        wrapper.innerHTML = modalHtml;
        attachModalListeners(wrapper, form, btns);
    };
    const changeHandler = (form, btns, event) => {
        const contentIdInput = form.querySelector('.ibexa-extra-actions__form-field--content-info');
        const locationInput = form.querySelector('.ibexa-extra-actions__form-field--location');
        const contentId = contentIdInput.value;
        const locationId = locationInput.value;
        const checkedBtn = event.currentTarget;
        const languageCode = checkedBtn.value;
        const checkVersionDraftLink = Routing.generate('ibexa.version_draft.has_no_conflict', { contentId, languageCode, locationId });

        fetch(checkVersionDraftLink, {
            credentials: 'same-origin',
        }).then((response) => {
            if (response.status === 409) {
                response.text().then(showModal.bind(null, form, btns));
            } else if (response.status === 200) {
                if (form.querySelector('#user_edit_version_info')) {
                    redirectToUserEdit(languageCode, contentId, form);

                    return;
                }

                form.submit();
            }
        });
    };
    const attachEventsToEditActionsWidget = (container) => {
        const btns = [...container.querySelectorAll('.form-check [type="radio"]')];
        const form = container.querySelector('form');

        btns.forEach((btn) => btn.addEventListener('change', changeHandler.bind(null, form, btns), false));
    };

    [...editActions].forEach(attachEventsToEditActionsWidget);
})(window, window.document, window.ibexa, window.bootstrap, window.Routing);
