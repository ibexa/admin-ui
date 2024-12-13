(function (global, doc, bootstrap, Translator) {
    let toggleAllTimeout;
    const MULTI_COLLAPSE_BTN_TAG = 'data-multi-collapse-btn-id';
    const COLLAPSE_SECTION_BODY_TAG = 'data-multi-collapse-body';
    const toggleAllBtns = [...doc.querySelectorAll(`[${MULTI_COLLAPSE_BTN_TAG}]`)];
    const multiCollapsBodies = [...doc.querySelectorAll(`[${COLLAPSE_SECTION_BODY_TAG}]`)];

    const toggleMultiCollapseButton = (btn, changeToCollapseAll) => {
        const displayedText = changeToCollapseAll
            ? /*@Desc("Collapse all)*/ 'product_type.edit.section.attribute_collapse_all'
            : /*@Desc("Expand all)*/ 'product_type.edit.section.attribute_expand_all';

        btn.innerHTML = Translator.trans(displayedText, {}, 'ibexa_product_catalog');
        btn.classList.toggle('ibexa-label-expand-all');
    };

    doc.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
        const toggleButton = collapseNode.querySelector('.ibexa-collapse__toggle-btn');
        const isCollapsed = toggleButton.classList.contains('collapsed');

        collapseNode.classList.toggle('ibexa-collapse--collapsed', isCollapsed);
        collapseNode.dataset.collapsed = isCollapsed;

        if (toggleAllBtns.length > 0) {
            const multiCollapseNode = collapseNode.closest(`[${COLLAPSE_SECTION_BODY_TAG}]`);

            if (!!multiCollapseNode) {
                const currentToggleAllButton = toggleAllBtns.find(
                    (button) => button.getAttribute(MULTI_COLLAPSE_BTN_TAG) === multiCollapseNode.getAttribute(COLLAPSE_SECTION_BODY_TAG),
                );

                collapseNode.querySelector('.ibexa-collapse__toggle-btn--status').addEventListener('click', (event) => {
                    event.stopPropagation();

                    const myToggleButtons = [...multiCollapseNode.querySelectorAll('.ibexa-collapse__toggle-btn--status')];

                    window.clearTimeout(toggleAllTimeout);

                    toggleAllTimeout = window.setTimeout(() => {
                        const countCollapsed = myToggleButtons.filter((button) => button.classList.contains('collapsed')).length;
                        const needToBeToggled = countCollapsed === myToggleButtons.length || countCollapsed === 0;

                        if (needToBeToggled) {
                            toggleMultiCollapseButton(currentToggleAllButton, countCollapsed === 0);
                        }
                    }, 200);
                });
            }
        }

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
    });

    const handleCollapseAction = (multiCollapseNode, expandAction) => {
        multiCollapseNode.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
            const isElementCollapsed = collapseNode.classList.contains('ibexa-collapse--collapsed');
            const needToBeToggled = expandAction === isElementCollapsed;

            if (needToBeToggled) {
                const element = bootstrap.Collapse.getOrCreateInstance(collapseNode.querySelector('.ibexa-multi-collapse-single-item'));
                expandAction ? element.show() : element.hide();
            }
        });
    };
    const attachAllElementsToggler = (btn) =>
        btn.addEventListener('click', () => {
            const collapseSelector = btn.getAttribute(MULTI_COLLAPSE_BTN_TAG);
            if (!!collapseSelector) {
                const multiCollapseNode = multiCollapsBodies.find(
                    (node) => node.getAttribute(COLLAPSE_SECTION_BODY_TAG) === collapseSelector,
                );

                window.clearTimeout(toggleAllTimeout);

                toggleAllTimeout = window.setTimeout(() => {
                    const isExpandingAction = btn.classList.contains('ibexa-label-expand-all');

                    handleCollapseAction(multiCollapseNode, isExpandingAction);
                    toggleMultiCollapseButton(btn, isExpandingAction);
                }, 200);
            }
        });

    toggleAllBtns.forEach((btn) => attachAllElementsToggler(btn));
})(window, window.document, window.bootstrap, window.Translator);
