(function (global, doc) {
    if (doc.querySelector('.ibexa-navigation-menu')) {
        return;
    }

    const EDIT_CONTENT_TOP_PADDING = 42;
    const formContainerNode = doc.querySelector('.ibexa-edit-content');
    const allSections = [...doc.querySelectorAll('.ibexa-anchor-navigation-sections__section, .ibexa-edit-content__secondary-section')];
    const isVerticalScrollVisible = () => {
        const { scrollHeight, offsetHeight } = formContainerNode;

        return scrollHeight > offsetHeight;
    };
    const removeStartingHashChar = (sectionId) => {
        if (sectionId && sectionId[0] === '#') {
            return sectionId.slice(1);
        }

        return sectionId;
    };
    const showSection = (sectionId) => {
        doc.querySelectorAll('.ibexa-anchor-navigation-menu__item-btn').forEach((btn) => {
            const { anchorTargetSectionId } = btn.dataset;

            btn.classList.toggle(
                'ibexa-anchor-navigation-menu__item-btn--active',
                removeStartingHashChar(anchorTargetSectionId) === removeStartingHashChar(sectionId),
            );
        });
    };
    const navigateTo = (event) => {
        const { anchorTargetSectionId } = event.currentTarget.dataset;
        const targetSection = [
            ...doc.querySelectorAll('.ibexa-anchor-navigation-sections__section, .ibexa-edit-content__secondary-section'),
        ].find((section) => {
            const sectionId = section.dataset.id || section.dataset.anchorSectionId;

            return removeStartingHashChar(sectionId) === removeStartingHashChar(anchorTargetSectionId);
        });

        if (isVerticalScrollVisible()) {
            formContainerNode.scrollTo({
                top: targetSection.offsetTop + EDIT_CONTENT_TOP_PADDING,
                behavior: 'smooth',
            });
        } else {
            showSection(anchorTargetSectionId);
        }
    };

    doc.querySelectorAll('.ibexa-anchor-navigation-menu__item-btn').forEach((btn) => {
        btn.addEventListener('click', navigateTo, false);
    });

    if (formContainerNode && allSections.length) {
        formContainerNode.addEventListener('scroll', () => {
            const position = formContainerNode.scrollTop;
            const activeSection = allSections.find((section) => {
                const start = section.offsetTop;
                const end = section.offsetHeight + section.offsetTop;

                return position >= start && position < end;
            });

            if (activeSection) {
                const activeSectionId = activeSection.dataset.id ?? activeSection.dataset.anchorSectionId;

                showSection(activeSectionId);
            }
        });
    }
})(window, window.document);
