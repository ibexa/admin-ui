(function (global, doc) {
    let activeFieldEdit = null;
    const FOCUS_MODE_ENABLE_EVENT_NAME = 'ibexa-focus-mode:enable';
    const FOCUS_MODE_DISABLE_EVENT_NAME = 'ibexa-focus-mode:disable';
    const focusModeEnableBtns = doc.querySelectorAll('.ibexa-field-edit__focus-mode-control-btn--enable');
    const focusModeDisbaleBtns = doc.querySelectorAll('.ibexa-field-edit__focus-mode-control-btn--disable');
    const changeFocusModeState = (active) => {
        if (!activeFieldEdit) {
            return;
        }

        const dispatchEventName = active ? FOCUS_MODE_ENABLE_EVENT_NAME : FOCUS_MODE_DISABLE_EVENT_NAME;
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
    const handleKeyPress = (event) => {
        if (event.key === 'Escape') {
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
        FOCUS_MODE_ENABLE_EVENT_NAME,
        () => {
            doc.body.addEventListener('keydown', handleKeyPress, false);
        },
        false,
    );
    doc.body.addEventListener(
        FOCUS_MODE_DISABLE_EVENT_NAME,
        () => {
            doc.body.removeEventListener('keydown', handleKeyPress, false);
        },
        false,
    );
})(window, window.document);
