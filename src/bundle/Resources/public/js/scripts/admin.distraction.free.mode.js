(function (global, doc) {
    let activeFieldEdit = null;
    let clearedPositionNodesData = [];
    const DISTRACTION_FREE_MODE_ENABLE_EVENT_NAME = 'ibexa-distraction-free:enable';
    const DISTRACTION_FREE_DISABLE_EVENT_NAME = 'ibexa-distraction-free:disable';
    const distractionFreeModeEnableBtns = doc.querySelectorAll('.ibexa-field-edit__distraction-free-mode-control-btn--enable');
    const distractionFreeModeDisableBtns = doc.querySelectorAll('.ibexa-field-edit__distraction-free-mode-control-btn--disable');
    const resetAncestorsPositions = (field) => {
        let parentElement = field.parentNode;

        while (parentElement && parentElement !== doc.body) {
            const { overflow, position } = getComputedStyle(parentElement);

            if (overflow !== 'visible' || position === 'absolute') {
                clearedPositionNodesData.push({
                    node: parentElement,
                    originalInlineOverflow: parentElement.style.overflow,
                    originalInlinePosition: parentElement.style.position,
                });

                parentElement.style.overflow = 'visible';
                parentElement.style.position = 'static';
            }

            parentElement = parentElement.parentNode;
        }
    }
    const restoreAncestorsPositions = () => {
        clearedPositionNodesData.forEach(({ node, originalInlineOverflow, originalInlinePosition }) => {
            if (originalInlineOverflow) {
                node.style.overflow = originalInlineOverflow;
            } else {
                node.style.removeProperty('overflow');
            }

            if (originalInlinePosition) {
                node.style.position = originalInlinePosition;
            } else {
                node.style.removeProperty('position');
            }
        });

        clearedPositionNodesData = [];
    }
    const changeDistractionFreeModeState = (active) => {
        if (!activeFieldEdit) {
            return;
        }

        const dispatchEventName = active ? DISTRACTION_FREE_MODE_ENABLE_EVENT_NAME : DISTRACTION_FREE_DISABLE_EVENT_NAME;
        const editorSourceElement = activeFieldEdit.querySelector('.ibexa-data-source__richtext');
        const editorInstance = editorSourceElement.ckeditorInstance;

        activeFieldEdit.classList.toggle('ibexa-field-edit--distraction-free-mode-active', active);
        editorInstance.set('distractionFreeModeActive', active);

        if (active) {
            resetAncestorsPositions(activeFieldEdit);
        } else {
            restoreAncestorsPositions();
        }

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
            changeDistractionFreeModeState(false);
        }
    };

    distractionFreeModeEnableBtns.forEach((btn) => {
        btn.addEventListener(
            'click',
            ({ currentTarget }) => {
                activeFieldEdit = currentTarget.closest('.ibexa-field-edit');
                changeDistractionFreeModeState(true);
            },
            false,
        );
    });
    distractionFreeModeDisableBtns.forEach((btn) => {
        btn.addEventListener('click', () => changeDistractionFreeModeState(false), false);
    });

    doc.body.addEventListener(
        DISTRACTION_FREE_MODE_ENABLE_EVENT_NAME,
        () => {
            doc.body.addEventListener('keydown', handleKeyPress, false);
        },
        false,
    );
    doc.body.addEventListener(
        DISTRACTION_FREE_DISABLE_EVENT_NAME,
        () => {
            doc.body.removeEventListener('keydown', handleKeyPress, false);
        },
        false,
    );
})(window, window.document);
