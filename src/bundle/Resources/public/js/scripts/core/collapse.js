(function (global, doc, bootstrap, Translator) {
    let toggleAllTimeout;
    const TOGGLE_TIMEOUT = 200;
    const toggleAllBtns = [...doc.querySelectorAll(`[data-multi-collapse-btn-id]`)];
    const toggleMultiCollapseBtn = (btn, changeToCollapseAll) => {
        const displayedText = changeToCollapseAll
            ? /*@Desc("Collapse all sections)*/ 'collapse.collapse_all'
            : /*@Desc("Expand all sections)*/ 'collapse.expand_all';

        btn.innerText = Translator.trans(displayedText, {}, 'ibexa_collapse');
        btn.classList.toggle('ibexa-multi-collapse__btn--expand-all-label', !changeToCollapseAll);
    };

    doc.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
        const toggleButton = collapseNode.querySelector('.ibexa-collapse__toggle-btn');
        const isCollapsed = toggleButton.classList.contains('collapsed');

        collapseNode.classList.toggle('ibexa-collapse--collapsed', isCollapsed);
        collapseNode.dataset.collapsed = isCollapsed;

        const multiCollapseNode = collapseNode.closest(`[data-multi-collapse-body]`);

        collapseNode.addEventListener('hide.bs.collapse', (event) => {
            event.stopPropagation();

            collapseNode.classList.add('ibexa-collapse--collapsed');
            collapseNode.dataset.collapsed = true;
        });

        collapseNode.addEventListener('show.bs.collapse', (event) => {
            event.stopPropagation();

            collapseNode.classList.remove('ibexa-collapse--collapsed');
            collapseNode.dataset.collapsed = false;
        });

        if (!multiCollapseNode || !toggleAllBtns.length) {
            return;
        }

        const currentToggleAllBtn = doc.querySelector(`[data-multi-collapse-btn-id="${multiCollapseNode.dataset.multiCollapseBody}"]`);
        const attachClickToggleHandler = (section) => {
            section.addEventListener('click', () => {
                const currentCollapsibleBtns = [...multiCollapseNode.querySelectorAll('[data-bs-toggle]')];

                global.clearTimeout(toggleAllTimeout);

                toggleAllTimeout = global.setTimeout(() => {
                    const collapsedCount = currentCollapsibleBtns.filter((btn) => btn.classList.contains('collapsed')).length;
                    const shouldBeToggled = collapsedCount === currentCollapsibleBtns.length || collapsedCount === 0;

                    if (shouldBeToggled) {
                        toggleMultiCollapseBtn(currentToggleAllBtn, collapsedCount === 0);
                    }
                }, TOGGLE_TIMEOUT);
            });
        };

        collapseNode.querySelectorAll('[data-bs-toggle]').forEach(attachClickToggleHandler);
    });

    const handleCollapseAction = (multiCollapseNode, isExpandAction) => {
        multiCollapseNode.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
            const isElementCollapsed = collapseNode.classList.contains('ibexa-collapse--collapsed');
            const shouldBeToggled = isExpandAction === isElementCollapsed;

            if (shouldBeToggled) {
                const element = bootstrap.Collapse.getOrCreateInstance(collapseNode.querySelector('.ibexa-multi-collapse__single-item'));
                if (isExpandAction) {
                    element.show();
                } else {
                    element.hide();
                }
            }
        });
    };
    const attachAllElementsToggler = (btn) => {
        btn.addEventListener('click', () => {
            const collapseId = btn.dataset.multiCollapseBtnId;

            if (!collapseId) {
                return;
            }

            const multiCollapseBodyNode = doc.querySelector(`[data-multi-collapse-body="${collapseId}"]`);

            global.clearTimeout(toggleAllTimeout);

            toggleAllTimeout = global.setTimeout(() => {
                const isExpandingAction = btn.classList.contains('ibexa-multi-collapse__btn--expand-all-label');

                handleCollapseAction(multiCollapseBodyNode, isExpandingAction);
                toggleMultiCollapseBtn(btn, isExpandingAction);
            }, TOGGLE_TIMEOUT);
        });
    };
    toggleAllBtns.forEach(attachAllElementsToggler);
})(window, window.document, window.bootstrap, window.Translator);
