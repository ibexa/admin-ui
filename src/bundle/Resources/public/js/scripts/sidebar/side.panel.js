(function (global, doc, ibexa) {
    const CLASS_HIDDEN = 'ibexa-side-panel--hidden';
    const sidePanelCloseBtns = doc.querySelectorAll(
        '.ibexa-side-panel .ids-button--close, .ibexa-side-panel .ibexa-side-panel__btn--cancel',
    );
    const sidePanelTriggers = [...doc.querySelectorAll('.ibexa-side-panel-trigger')];
    const panelBackdrops = new Map();
    const defaultBackdrop = new ibexa.core.Backdrop();
    const removeBackdrop = (sidePanel) => {
        const backdrop = panelBackdrops.get(sidePanel) || defaultBackdrop;

        backdrop.remove();
        doc.body.classList.remove('ibexa-scroll-disabled');

        if (panelBackdrops.has(sidePanel)) {
            panelBackdrops.delete(sidePanel);
        }
    };
    const showBackdrop = (sidePanel) => {
        if (sidePanel.dataset.backdropClasses) {
            const extraClasses = sidePanel.dataset.backdropClasses.split(' ').filter(Boolean);
            const newBackdrop = new ibexa.core.Backdrop({ extraClasses });

            newBackdrop.show();
            panelBackdrops.set(sidePanel, newBackdrop);
        } else {
            defaultBackdrop.show();
            panelBackdrops.set(sidePanel, defaultBackdrop);
        }

        doc.body.classList.add('ibexa-scroll-disabled');
    };
    const toggleSidePanelVisibility = (sidePanel) => {
        const shouldBeVisible = sidePanel.classList.contains(CLASS_HIDDEN);
        const handleClickOutside = (event) => {
            const currentBackdrop = panelBackdrops.get(sidePanel);

            if (event.target.classList.contains('ibexa-backdrop') && event.target === currentBackdrop.get()) {
                event.stopPropagation();
                sidePanel.classList.add(CLASS_HIDDEN);
                doc.body.removeEventListener('click', handleClickOutside, { capture: true });
                removeBackdrop(sidePanel);

                if (sidePanel.dataset?.closeReload === 'true') {
                    global.location.reload();
                }
            }
        };

        sidePanel.classList.toggle(CLASS_HIDDEN, !shouldBeVisible);

        if (shouldBeVisible) {
            doc.body.addEventListener('click', handleClickOutside, { capture: true });
            showBackdrop(sidePanel);
        } else {
            doc.body.removeEventListener('click', handleClickOutside, { capture: true });
            removeBackdrop(sidePanel);
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
