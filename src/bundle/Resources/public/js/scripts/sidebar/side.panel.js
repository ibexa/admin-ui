(function (global, doc, ibexa) {
    const CLASS_HIDDEN = 'ibexa-side-panel--hidden';
    const closeBtns = doc.querySelectorAll(
        '.ibexa-side-panel .ibexa-btn--close, .ibexa-side-panel .ibexa-side-panel__btn--cancel',
    );
    const btns = [...doc.querySelectorAll('.ibexa-btn--side-panel-actions')];
    const backdrop = new ibexa.core.Backdrop();
    const haveHiddenPart = (element) => element.classList.contains(CLASS_HIDDEN);
    const removeBackdrop = () => {
        backdrop.hide();
        doc.body.classList.remove('ibexa-scroll-disabled');
    };
    const allActivityBtn = doc?.querySelector('.ibexa-notifications__view-all-btn');

    const closeExtraActions = (actions) => {
        actions.classList.add(CLASS_HIDDEN);

        doc.body.dispatchEvent(new CustomEvent('ibexa-side-panel:after-close'));

        removeBackdrop();
    };
    const toggleExtraActionsWidget = (event) => {
        const actions = doc.querySelector(`.ibexa-side-panel[data-actions="create"]`);
        const isHidden = haveHiddenPart(actions);
        const detectClickOutside = (event) => {
            if (event.target.classList.contains('ibexa-backdrop')) {
                closeExtraActions(actions);
                doc.body.removeEventListener('click', detectClickOutside, false);
            }
        };

        actions.classList.toggle(CLASS_HIDDEN, !isHidden);

        if (!actions.classList.contains(CLASS_HIDDEN)) {
            backdrop.show();
            doc.body.addEventListener('click', detectClickOutside, false);
            doc.body.classList.add('ibexa-scroll-disabled');
        } else {
            doc.body.removeEventListener('click', detectClickOutside);
            removeBackdrop();
        }
    };

    const hideMenu = (btn) => {
        const menuBranch = btn.closest('.ibexa-multilevel-popup-menu__branch');

        if (!menuBranch?.menuInstanceElement) {
            return;
        }

        const menuInstance = ibexa.helpers.objectInstances.getInstance(menuBranch.menuInstanceElement);

        menuInstance.closeMenu();
    };
    const goToActivityLog = () => {
        window.location.href = Routing.generate('ibexa.notifications.render.all');
    };
    
    btns.forEach((btn) => {
        const { dataset } = btn;

        btn.addEventListener(
            'click',
            () => {
                toggleExtraActionsWidget(dataset);
                hideMenu(btn);
            },
            false,
        );
    });
    doc.body.addEventListener('ibexa-side-panel', (event) => toggleExtraActionsWidget(event), false);
    closeBtns.forEach((closeBtn) =>
        closeBtn.addEventListener(
            'click',
            (event) => {
                closeExtraActions(event.currentTarget.closest('.ibexa-side-panel'));
            },
            false,
        ),
    );
    allActivityBtn?.addEventListener('click', goToActivityLog, false);

})(window, window.document, window.ibexa);
