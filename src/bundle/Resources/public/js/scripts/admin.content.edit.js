(function (global, doc, ibexa, Translator, moment) {
    const ENTER_KEY_CODE = 13;
    const STATUS_ERROR = 'error';
    const STATUS_OFF = 'off';
    const STATUS_ON = 'on';
    const STATUS_SAVED = 'saved';
    const STATUS_SAVING = 'saving';
    const inputTypeToPreventSubmit = [
        'checkbox',
        'color',
        'date',
        'datetime-local',
        'email',
        'file',
        'image',
        'month',
        'number',
        'radio',
        'range',
        'reset',
        'search',
        'select-one',
        'select-multiple',
        'tel',
        'text',
        'time',
        'url',
    ];
    const form = doc.querySelector('.ibexa-form-validate');
    const submitBtns = form.querySelectorAll('[type="submit"]:not([formnovalidate])');
    const menuButtonsToValidate = doc.querySelectorAll('button[data-validate]');
    const fields = doc.querySelectorAll('.ibexa-field-edit');
    const getValidationResults = (validator) => {
        const isValid = validator.isValid();
        const validatorName = validator.constructor.name;
        const result = { isValid, validatorName };

        return result;
    };
    const getInvalidSections = (validator) => {
        return validator.fieldsToValidate.reduce((invalidSections, field) => {
            const section = field.item.closest('.ibexa-anchor-navigation-sections__section');

            if (section && field.item.classList.contains('is-invalid')) {
                invalidSections.add(section.dataset.anchorSectionId);
            }

            return invalidSections;
        }, new Set());
    };
    const focusOnFirstError = () => {
        const invalidFields = doc.querySelectorAll('.ibexa-field-edit.is-invalid');

        if (!invalidFields.length) {
            return;
        }

        const invalidSection = invalidFields[0].closest('.ibexa-anchor-navigation-sections__section');

        fields.forEach((field) => field.removeAttribute('tabindex'));
        invalidFields.forEach((field) => field.setAttribute('tabindex', '-1'));

        if (invalidSection) {
            const { anchorSectionId } = invalidSection.dataset;
            const invalidButton = doc.querySelector(`[data-anchor-target-section-id="${anchorSectionId}"`);

            invalidButton.click();
        }

        invalidFields[0].focus();
    };
    const clickHandler = (event) => {
        const btn = event.currentTarget;

        if (parseInt(btn.dataset.isFormValid, 10)) {
            return;
        }

        event.preventDefault();

        if (isFormValid(btn)) {
            // for some reason trying to fire click event inside the event handler flow is impossible
            // the following line breaks the flow so it's possible to fire click event on a button again.
            global.setTimeout(() => btn.click(), 0);
        }
    };
    const validateHandler = (event) => {
        event.preventDefault();

        const btn = event.currentTarget;

        btn.dataset.isFormValid = 0;

        isFormValid(btn);
    };
    const isFormValid = (btn) => {
        const validators = ibexa.fieldTypeValidators;
        const validationResults = validators.map(getValidationResults);
        const isValid = validationResults.every((result) => result.isValid);
        const invalidSections = validators.map(getInvalidSections);

        if (isValid) {
            btn.dataset.isFormValid = 1;

            return true;
        }

        const allValidatorsWithErrors = validationResults.filter((result) => !result.isValid).map((result) => result.validatorName);

        btn.dataset.validatorsWithErrors = [...new Set(allValidatorsWithErrors)].join();
        fields.forEach((field) => field.removeAttribute('id'));

        doc.querySelectorAll('.ibexa-anchor-navigation-menu__btn').forEach((anchorBtn) => {
            anchorBtn.classList.remove('ibexa-anchor-navigation-menu__btn--invalid');
        });

        invalidSections.forEach((sections) => {
            sections.forEach((invalidSectionId) => {
                doc.querySelector(`[data-anchor-target-section-id='${invalidSectionId}']`).classList.add(
                    'ibexa-anchor-navigation-menu__btn--invalid',
                );
            });
        });

        focusOnFirstError();

        return false;
    };
    const isAutosaveEnabled = () => {
        return ibexa.adminUiConfig.autosave.enabled && form.querySelector('[name="ezplatform_content_forms_content_edit[autosave]"]');
    };

    if (isAutosaveEnabled()) {
        const AUTOSAVE_SUBMIT_BUTTON_NAME = 'ezplatform_content_forms_content_edit[autosave]';
        const autosave = doc.querySelector('.ibexa-autosave');
        const autosaveStatusSavedNode = autosave.querySelector('.ibexa-autosave__status-saved');
        let currentAutosaveStatus = autosave.classList.contains('ibexa-autosave--on') ? STATUS_ON : STATUS_OFF;
        const generateCssStatusClass = (status) => `ibexa-autosave--${status}`;
        const setAutosaveStatus = (newStatus) => {
            if (!autosave) {
                return;
            }

            const oldCssStatusClass = generateCssStatusClass(currentAutosaveStatus);
            const newCssStatusClass = generateCssStatusClass(newStatus);

            autosave.classList.remove(oldCssStatusClass);
            autosave.classList.remove('ibexa-autosave--saved');
            autosave.classList.add(newCssStatusClass);

            currentAutosaveStatus = newStatus;
        };
        const setDraftSavedMessage = () => {
            if (!autosave) {
                return;
            }

            const userPreferredTimezone = ibexa.adminUiConfig.timezone;
            const saveDate = ibexa.helpers.timezone.convertDateToTimezone(new Date(), userPreferredTimezone);
            const saveTime = moment(saveDate).formatICU('HH:mm');
            const saveMessage = Translator.trans(
                /*@Desc("Autosave is on, draft created %time%")*/ 'content_edit.autosave.status_saved.message.full',
                { time: saveTime },
                'ibexa_content',
            );

            autosaveStatusSavedNode.innerHTML = saveMessage;
            autosave.classList.add('ibexa-autosave--saved');
        };

        setInterval(() => {
            const formData = new FormData(form);

            formData.set(AUTOSAVE_SUBMIT_BUTTON_NAME, true);
            setAutosaveStatus(STATUS_SAVING);

            fetch(form.target || window.location.href, { method: 'POST', body: formData })
                .then(ibexa.helpers.request.getStatusFromResponse)
                .then(() => {
                    setAutosaveStatus(STATUS_SAVED);
                    setDraftSavedMessage();
                })
                .catch(() => {
                    setAutosaveStatus(STATUS_ERROR);
                });
        }, ibexa.adminUiConfig.autosave.interval);
    }

    form.setAttribute('novalidate', true);
    form.onkeypress = (event) => {
        const keyCode = event.charCode || event.keyCode || 0;
        const activeElementType = typeof doc.activeElement.type !== 'undefined' ? doc.activeElement.type.toLowerCase() : '';

        if (keyCode === ENTER_KEY_CODE && inputTypeToPreventSubmit.includes(activeElementType)) {
            event.preventDefault();
        }
    };

    submitBtns.forEach((btn) => {
        btn.dataset.isFormValid = 0;
        btn.addEventListener('click', clickHandler, false);
    });

    menuButtonsToValidate.forEach((btn) => {
        btn.addEventListener('click', validateHandler, false);
    });
})(window, window.document, window.ibexa, window.Translator, window.moment);
