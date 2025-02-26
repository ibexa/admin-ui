import { getInstance } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/object.instances';

(function (global, doc, ibexa) {
    const CLASS_HIDDEN = 'ibexa-extra-actions--hidden';
    const CLASS_EXPANDED = 'ibexa-context-menu--expanded';
    const CLASS_PREVENT_SHOW = 'ibexa-extra-actions--prevent-show';
    const closeBtns = doc.querySelectorAll(
        '.ibexa-extra-actions .ibexa-btn--close, .ibexa-extra-actions .ibexa-extra-actions__btn--cancel',
    );
    const btns = [...doc.querySelectorAll('.ibexa-btn--extra-actions')];
    const menu = doc.querySelector('.ibexa-context-menu');
    const backdrop = new ibexa.core.Backdrop();
    const formsInitialData = new Map();
    const saveInitialFormData = (extraActionsContainer) => {
        const extraActionsInputs = extraActionsContainer.querySelectorAll('input, select');

        extraActionsInputs.forEach((node) => {
            const value = node.type === 'radio' || node.type === 'checkbox' ? node.checked : node.value;

            formsInitialData.set(node, value);
        });
    };
    const restoreInitialFormData = (extraActionsContainer) => {
        if (formsInitialData.size === 0) {
            return;
        }

        const extraActionsInputs = extraActionsContainer.querySelectorAll('input, select');

        extraActionsInputs.forEach((node) => {
            const value = formsInitialData.get(node);
            let prevValue = node.value;

            if (node.type === 'radio' || node.type === 'checkbox') {
                prevValue = node.checked;

                node.checked = value;
            } else if (node.tagName === 'SELECT') {
                const dropdownContainer = node.closest('.ibexa-dropdown');

                if (dropdownContainer) {
                    const dropdownInstance = getInstance(dropdownContainer);

                    dropdownInstance.selectOption(value);
                } else {
                    node.value = value;
                }
            } else {
                node.value = value;
            }

            if (value !== prevValue) {
                node.dispatchEvent(new CustomEvent('change'));
            }
        });
    };
    const haveHiddenPart = (element) => element.classList.contains(CLASS_HIDDEN) && !element.classList.contains(CLASS_PREVENT_SHOW);
    const removeBackdrop = () => {
        backdrop.hide();
        doc.body.classList.remove('ibexa-scroll-disabled');
    };
    const closeExtraActions = (actions) => {
        actions.classList.add(CLASS_HIDDEN);

        if (menu) {
            menu.classList.remove(CLASS_EXPANDED);
        }

        doc.body.dispatchEvent(new CustomEvent('ibexa-extra-actions:after-close'));

        removeBackdrop();
        restoreInitialFormData(actions);
    };
    const toggleExtraActionsWidget = (widgetData) => {
        const actions = doc.querySelector(`.ibexa-extra-actions[data-actions="${widgetData.actions}"]`);

        if (widgetData.validate && !parseInt(widgetData.isFormValid, 10)) {
            return;
        }

        const isHidden = haveHiddenPart(actions);
        const focusElement = actions.querySelector(widgetData.focusElement);
        const detectClickOutside = (event) => {
            if (event.target.classList.contains('ibexa-backdrop')) {
                closeExtraActions(actions);
                doc.body.removeEventListener('click', detectClickOutside, false);
            }
        };

        actions.classList.toggle(CLASS_HIDDEN, !isHidden);

        if (menu) {
            menu.classList.toggle(CLASS_EXPANDED, isHidden);
        }

        if (!actions.classList.contains(CLASS_HIDDEN)) {
            backdrop.show();
            doc.body.addEventListener('click', detectClickOutside, false);
            doc.body.classList.add('ibexa-scroll-disabled');
            saveInitialFormData(actions);
        } else {
            doc.body.removeEventListener('click', detectClickOutside);
            removeBackdrop();
            restoreInitialFormData(actions);
        }

        if (focusElement) {
            focusElement.focus();
        }
    };
    const initExtraActionsWidget = (dataset) => {
        const hashes = window.location.hash.split('#');

        if (hashes.includes(dataset.actions)) {
            toggleExtraActionsWidget(dataset);
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
        initExtraActionsWidget(dataset);
    });
    doc.body.addEventListener('ibexa-extra-actions:toggle-widget', (event) => toggleExtraActionsWidget(event.detail), false);
    closeBtns.forEach((closeBtn) =>
        closeBtn.addEventListener(
            'click',
            (event) => {
                closeExtraActions(event.currentTarget.closest('.ibexa-extra-actions'));
            },
            false,
        ),
    );
})(window, window.document, window.ibexa);
