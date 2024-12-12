(function (global, doc, bootstrap, Translator) {
    let toggleAllTimeout;
    const MULTI_COLLAPSE_BTN_TAG = 'data-multi-collapse-btn-id';
    const COLLAPSE_SECTION_BODY_TAG = 'data-multi-collapse-body';
    const toggleAllBtns = [...doc.querySelectorAll(`[${MULTI_COLLAPSE_BTN_TAG}]`)];
    const multiCollapsBodies = [...doc.querySelectorAll(`[${COLLAPSE_SECTION_BODY_TAG}]`)];

    const initializeClickedStateArray = () => {
        const initToggleState = [];

        toggleAllBtns.forEach((collapseBtn) => {
            const tagValue = collapseBtn.getAttribute(MULTI_COLLAPSE_BTN_TAG);
            let existingEntry = initToggleState.find((obj) => obj.btnName === tagValue);

            if (!existingEntry) {
                existingEntry = { btnName: tagValue, collapsedElements: [] };
                initToggleState.push(existingEntry);
            }
        });

        return initToggleState;
    };
    const clickedElementsState = initializeClickedStateArray();
    const toggleMultiCollapseButton = (btn, changeToCollapseAll) => {
        if (changeToCollapseAll && btn.classList.contains('label-expand-all')) {
            btn.innerHTML = Translator.trans(
                /*@Desc("Change collapseAll button label*/ 'product_type.edit.section.attribute_collapse_all',
                {},
                'ibexa_product_catalog',
            );
            btn.classList.remove('label-expand-all');

            return;
        }
        if (!changeToCollapseAll) {
            btn.innerHTML = Translator.trans(
                /*@Desc("Change collapseAll button label*/ 'product_type.edit.section.attribute_expand_all',
                {},
                'ibexa_product_catalog',
            );
            btn.classList.add('label-expand-all');
        }
    };
    const toggleMultiCollapseIfNeeded = (multiCollapseNode, toggleBtn, tableLength) => {
        const allElements = multiCollapseNode.querySelectorAll('.ibexa-multi-collapse--item');

        if (tableLength === allElements.length || tableLength === 0) {
            toggleMultiCollapseButton(toggleBtn, tableLength === 0);
        }
    };
    const handleCollapseAction = (multiCollapseNode, expandAction) => {
        const index = clickedElementsState.findIndex(
            (element) => element.btnName === multiCollapseNode.getAttribute(COLLAPSE_SECTION_BODY_TAG),
        );

        if (expandAction) {
            clickedElementsState[index].collapsedElements = [];
        }

        multiCollapseNode.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
            const isElementCollapsed = collapseNode.classList.contains('ibexa-collapse--collapsed');
            const needToBeToggled = expandAction === isElementCollapsed;

            if (needToBeToggled) {
                const element = bootstrap.Collapse.getOrCreateInstance(collapseNode.querySelector('.ibexa-multi-collapse--item'));
                expandAction ? element.show() : element.hide();

                if (!expandAction) {
                    const uniqueName = collapseNode.querySelector('.ibexa-collapse__toggle-btn').getAttribute('data-bs-target');
                    clickedElementsState[index].collapsedElements.push(uniqueName);
                }
            }
        });
    };

    doc.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
        const toggleButton = collapseNode.querySelector('.ibexa-collapse__toggle-btn');
        const isCollapsed = toggleButton.classList.contains('collapsed');

        collapseNode.classList.toggle('ibexa-collapse--collapsed', isCollapsed);
        collapseNode.dataset.collapsed = isCollapsed;

        if (toggleAllBtns.length > 0) {
            const multiCollapseNode = collapseNode.closest(`[${COLLAPSE_SECTION_BODY_TAG}]`);

            if (!!multiCollapseNode) {
                const uniqueName = toggleButton.getAttribute('data-bs-target');
                const currentToggleAllButton = toggleAllBtns.find(
                    (button) => button.getAttribute(MULTI_COLLAPSE_BTN_TAG) === multiCollapseNode.getAttribute(COLLAPSE_SECTION_BODY_TAG),
                );

                collapseNode.querySelector('.ibexa-collapse__toggle-btn--status').addEventListener('click', (event) => {
                    event.stopPropagation();

                    const collapseSectionIndex = clickedElementsState.findIndex(
                        (collapseSection) => collapseSection.btnName === currentToggleAllButton.getAttribute(MULTI_COLLAPSE_BTN_TAG),
                    );
                    const toggleIndex = clickedElementsState[collapseSectionIndex].collapsedElements.findIndex(
                        (element) => element === uniqueName,
                    );

                    window.clearTimeout(toggleAllTimeout);

                    toggleAllTimeout = window.setTimeout(() => {
                        if (toggleIndex !== -1) {
                            clickedElementsState[collapseSectionIndex].collapsedElements.splice(toggleIndex, 1);

                            toggleMultiCollapseIfNeeded(
                                multiCollapseNode,
                                currentToggleAllButton,
                                clickedElementsState[collapseSectionIndex].collapsedElements.length,
                            );

                            return;
                        }
                        clickedElementsState[collapseSectionIndex].collapsedElements.push(uniqueName);

                        toggleMultiCollapseIfNeeded(
                            multiCollapseNode,
                            currentToggleAllButton,
                            clickedElementsState[collapseSectionIndex].collapsedElements.length,
                        );
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

    const attachAllElementsToggler = (btn) =>
        btn.addEventListener('click', () => {
            const collapseSelector = btn.getAttribute(MULTI_COLLAPSE_BTN_TAG);
            if (!!collapseSelector) {
                const multiCollapseNode = multiCollapsBodies.find(
                    (node) => node.getAttribute(COLLAPSE_SECTION_BODY_TAG) === collapseSelector,
                );

                window.clearTimeout(toggleAllTimeout);

                toggleAllTimeout = window.setTimeout(() => {
                    const isExpandingAction = btn.classList.contains('label-expand-all');

                    handleCollapseAction(multiCollapseNode, isExpandingAction);
                    toggleMultiCollapseButton(btn, isExpandingAction);
                }, 200);
            }
        });

    toggleAllBtns.forEach((btn) => attachAllElementsToggler(btn));
})(window, window.document, window.bootstrap, window.Translator);
