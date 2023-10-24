(function (global, doc) {
    let activeFieldEdit = null;
    const ENABLE_FOCUS_MODE_EVENT_NAME = 'ibexa-focus-mode:on';
    const DISABLE_FOCUS_MODE_EVENT_NAME = 'ibexa-focus-mode:off';
    const focusModeEnableBtns = doc.querySelectorAll('.ibexa-field-edit__focus-mode-control-btn--enable');
    const focusModeDisbaleBtns = doc.querySelectorAll('.ibexa-field-edit__focus-mode-control-btn--disable');
    const changeFocusModeState = (active) => {
        if (!activeFieldEdit) {
            return;
        }

        const dispatchEventName = active ? ENABLE_FOCUS_MODE_EVENT_NAME : DISABLE_FOCUS_MODE_EVENT_NAME;
        const editorSourceElement = activeFieldEdit.querySelector('.ibexa-data-source__richtext');
        const editorInstance = editorSourceElement.ckeditorInstance;

        activeFieldEdit.classList.toggle('ibexa-field-edit--focus-mode-active', active);
        editorInstance.set('focusModeActive', active);

        doc.body.dispatchEvent(
            new CustomEvent(dispatchEventName, {
                detail: {
                    activeFieldEdit,
                },
            }),
        );

        if (!active) {
            activeFieldEdit = null;
        }
    };
    const watchDisableFocusModeByKeyboard = (event) => {
        if (event.key === 'Escape' || event.keyCode === 27) {
            changeFocusModeState(false);
        }
    };

    focusModeEnableBtns.forEach((btn) => {
        btn.addEventListener(
            'click',
            ({ currentTarget }) => {
                activeFieldEdit = currentTarget.closest('.ibexa-field-edit');
                changeFocusModeState(true);
            },
            false,
        );
    });
    focusModeDisbaleBtns.forEach((btn) => {
        btn.addEventListener('click', () => changeFocusModeState(false), false);
    });

    doc.body.addEventListener(
        ENABLE_FOCUS_MODE_EVENT_NAME,
        () => doc.body.addEventListener('keydown', watchDisableFocusModeByKeyboard),
        false,
    );
    doc.body.addEventListener(
        DISABLE_FOCUS_MODE_EVENT_NAME,
        () => doc.body.removeEventListener('keydown', watchDisableFocusModeByKeyboard),
        false,
    );
})(window, window.document);
