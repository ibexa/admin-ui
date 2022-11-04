(function (global, doc, ibexa) {
    const resizeEllipsisObserver = new ResizeObserver((entries) => {
        entries.forEach((entry) => {
            parseAll(entry.target);
        });
    });
    const parseAll = (baseElement = doc) => {
        if (!baseElement) {
            return;
        }

        const middleEllipsisContainers = [...doc.querySelectorAll('.ibexa-middle-ellipsis')];

        if (baseElement instanceof Element) {
            middleEllipsisContainers.push(baseElement);
        }

        middleEllipsisContainers.forEach((middleEllipsisContainer) => {
            const partStart = middleEllipsisContainer.querySelector('.ibexa-middle-ellipsis__name--start');

            middleEllipsisContainer.classList.toggle('ibexa-middle-ellipsis--ellipsized', partStart.scrollWidth > partStart.offsetWidth);
            ibexa.helpers.tooltips.parse(middleEllipsisContainer);

            resizeEllipsisObserver.observe(middleEllipsisContainer);
        });
    };
    const update = (baseElement, content) => {
        const contentElements = [...baseElement.querySelectorAll('.ibexa-middle-ellipsis__name-ellipsized')];

        baseElement.dataset.bsOriginalTitle = content;
        contentElements.forEach((contentElement) => {
            contentElement.innerHTML = content;
        });
        parseAll(baseElement);
    };

    ibexa.addConfig('helpers.ellipsis.middle', {
        parseAll,
        update,
    });
})(window, window.document, window.ibexa);
