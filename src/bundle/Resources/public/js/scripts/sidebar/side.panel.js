(function (global, doc, ibexa) {
    const CLASS_HIDDEN = 'ibexa-side-panel--hidden';
    const sidePanelCloseBtns = doc.querySelectorAll(
        '.ibexa-side-panel .ibexa-btn--close, .ibexa-side-panel .ibexa-side-panel__btn--cancel',
    );
    const sidePanelTriggers = [...doc.querySelectorAll('.ibexa-side-panel-trigger')];
    const backdrop = new ibexa.core.Backdrop();
    const removeBackdrop = () => {
        backdrop.hide();
        doc.body.classList.remove('ibexa-scroll-disabled');
    };
    const showBackdrop = () => {
        backdrop.show();
        doc.body.classList.add('ibexa-scroll-disabled');
    };
    const toggleSidePanelVisibility = (sidePanel) => {
        const shouldBeVisible = sidePanel.classList.contains(CLASS_HIDDEN);
        const handleClickOutside = (event) => {
            if (event.target.classList.contains('ibexa-backdrop')) {
                sidePanel.classList.add(CLASS_HIDDEN);
                doc.body.removeEventListener('click', handleClickOutside, false);
                removeBackdrop();
            }
        };

        sidePanel.classList.toggle(CLASS_HIDDEN, !shouldBeVisible);

        if (shouldBeVisible) {
            doc.body.addEventListener('click', handleClickOutside, false);
            showBackdrop();
        } else {
            doc.body.removeEventListener('click', handleClickOutside, false);
            removeBackdrop();
        }
    };

    sidePanelTriggers.forEach((trigger) => {
        trigger.addEventListener(
            'click',
            (event) => {
                toggleSidePanelVisibility(doc.querySelector(event.currentTarget.dataset.sidePanelSelector));
            },
            false,
        );
    });

    sidePanelCloseBtns.forEach((closeBtn) =>
        closeBtn.addEventListener(
            'click',
            (event) => {
                toggleSidePanelVisibility(event.currentTarget.closest('.ibexa-side-panel'));
            },
            false,
        ),
    );
})(window, window.document, window.ibexa);
