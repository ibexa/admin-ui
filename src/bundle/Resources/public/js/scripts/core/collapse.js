(function (global, doc, bootstrap) {
    let toggleAllTimeout;
    let singleElementClicked = [];
    const toggleAllBtn = doc.querySelector('.ibexa-multi-collapse-btn');
    const expandAll = toggleAllBtn?.querySelector('.ibexa-attribute-group__toggler-expand');
    const collapseAll = toggleAllBtn?.querySelector('.ibexa-attribute-group__toggler-collapse');
    const MULTI_COLLAPSE_BODY_SELECTOR = '.ibexa-multi-collapse';
    const toggleMultiCollapseButton = () => {
        collapseAll.classList.toggle('d-none');
        expandAll.classList.toggle('d-none');
    };
    const toggleMultiCollapseIfNeeded = () => {
        const allGroups = doc.querySelectorAll(MULTI_COLLAPSE_BODY_SELECTOR);

        if (singleElementClicked.length === allGroups.length || singleElementClicked.length === 0) {
            toggleMultiCollapseButton();
        }
    };
    const handleCollapseAction = (expandAction) => {
        singleElementClicked = [];
        doc.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
            const isElementCollapsed = collapseNode.classList.contains('ibexa-collapse--collapsed');

            if (expandAction === isElementCollapsed) {
                bootstrap.Collapse.getOrCreateInstance(collapseNode.querySelector(MULTI_COLLAPSE_BODY_SELECTOR)).toggle();
            }
        });
    };

    doc.querySelectorAll('.ibexa-collapse').forEach((collapseNode) => {
        const toggleButton = collapseNode.querySelector('.ibexa-collapse__toggle-btn');
        const isCollapsed = toggleButton.classList.contains('collapsed');

        collapseNode.classList.toggle('ibexa-collapse--collapsed', isCollapsed);
        collapseNode.dataset.collapsed = isCollapsed;

        if (toggleAllBtn) {
            const uniqueName = toggleButton.getAttribute('data-bs-target');

            collapseNode.addEventListener('click', (event) => {
                event.stopPropagation();

                const toggleIndex = singleElementClicked.findIndex((elementIndex) => elementIndex === uniqueName);

                window.clearTimeout(toggleAllTimeout);

                toggleAllTimeout = window.setTimeout(() => {
                    if (toggleIndex !== -1) {
                        singleElementClicked.splice(toggleIndex, 1);
                        toggleMultiCollapseIfNeeded();

                        return;
                    }

                    singleElementClicked.push(uniqueName);
                    toggleMultiCollapseIfNeeded();
                }, 200);
            });
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

    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', (event) => {
            event.stopPropagation();

            window.clearTimeout(toggleAllTimeout);

            toggleAllTimeout = window.setTimeout(() => {
                const isExpanding = collapseAll.classList.contains('d-none');

                handleCollapseAction(isExpanding);
                toggleMultiCollapseButton();
            }, 200);
        });
    }
})(window, window.document, window.bootstrap);
