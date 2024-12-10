(function (global, doc, bootstrap) {
    let toggleAllTimeout;
    const MULTI_COLLAPSE_BTN_TAG = 'data-multi-collapse-btn-id';
    const COLLAPSE_SECTION_BODY_TAG = 'data-multi-collapse-body';
    const MULTI_COLLAPSE_ELEMENT_SELECTOR = '.ibexa-multicollapse--item';
    const toggleAllBtns = doc.querySelectorAll(`[${MULTI_COLLAPSE_BTN_TAG}]`);
    const multiCollapsBodies = doc.querySelectorAll(`[${COLLAPSE_SECTION_BODY_TAG}]`);

    const initializeClickedStateArray = () => {
        const initToggleState = [];

        if (toggleAllBtns.length === 0) return [];

        Array.from(toggleAllBtns).forEach((collapseBtn) => {
            const tagValue = collapseBtn.getAttribute(MULTI_COLLAPSE_BTN_TAG);
            let existingEntry = initToggleState.find((obj) => obj.btnName === tagValue);

            if (!existingEntry) {
                existingEntry = { btnName: tagValue, collapsedElements: [] };
                initToggleState.push(existingEntry);
            }
        });

        return initToggleState;
    };
    let clickedElementsState = initializeClickedStateArray();
    const toggleMultiCollapseButton = (btn, changeToCollapseAll) => {
        const expandAll = btn.querySelector('.ibexa-multi-collapse__toggler-expand');
        const collapseAll = btn.querySelector('.ibexa-multi-collapse__toggler-collapse');

        if (changeToCollapseAll && collapseAll.classList.contains('d-none')) {
            collapseAll.classList.toggle('d-none');
            expandAll.classList.toggle('d-none');
        } else if (!changeToCollapseAll && expandAll.classList.contains('d-none')) {
            collapseAll.classList.toggle('d-none');
            expandAll.classList.toggle('d-none');
        }
    };
    const toggleMultiCollapseIfNeeded = (multiCollapseNode, toggleBtn, tabllLength) => {
        const allElements = multiCollapseNode.querySelectorAll(MULTI_COLLAPSE_ELEMENT_SELECTOR);

        if (tabllLength === allElements.length || tabllLength === 0) {
            toggleMultiCollapseButton(toggleBtn, tabllLength === 0);
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

            if (expandAction === isElementCollapsed) {
                bootstrap.Collapse.getOrCreateInstance(collapseNode.querySelector(MULTI_COLLAPSE_ELEMENT_SELECTOR)).toggle();

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

        if (toggleAllBtns && toggleAllBtns.length > 0) {
            const multicollapseNode = collapseNode.closest(`[${COLLAPSE_SECTION_BODY_TAG}]`);

            if (!!multicollapseNode) {
                const uniqueName = toggleButton.getAttribute('data-bs-target');
                const currentToggleAllButton = Array.from(toggleAllBtns).find(
                    (button) => button.getAttribute(MULTI_COLLAPSE_BTN_TAG) === multicollapseNode.getAttribute(COLLAPSE_SECTION_BODY_TAG),
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
                                multicollapseNode,
                                currentToggleAllButton,
                                clickedElementsState[collapseSectionIndex].collapsedElements.length,
                            );

                            return;
                        }
                        clickedElementsState[collapseSectionIndex].collapsedElements.push(uniqueName);

                        toggleMultiCollapseIfNeeded(
                            multicollapseNode,
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

    if (toggleAllBtns) {
        toggleAllBtns.forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.stopPropagation();

                const collapseAll = btn?.querySelector('.ibexa-multi-collapse__toggler-collapse');
                const collapseSelector = btn.getAttribute(MULTI_COLLAPSE_BTN_TAG);
                if (!!collapseSelector) {
                    const multiCollapseNode = Array.from(multiCollapsBodies).find(
                        (node) => node.getAttribute(COLLAPSE_SECTION_BODY_TAG) === collapseSelector,
                    );

                    window.clearTimeout(toggleAllTimeout);

                    toggleAllTimeout = window.setTimeout(() => {
                        const isExpandingAction = collapseAll.classList.contains('d-none');

                        handleCollapseAction(multiCollapseNode, isExpandingAction);
                        toggleMultiCollapseButton(btn, isExpandingAction);
                    }, 200);
                }
            });
        });
    }
})(window, window.document, window.bootstrap);
