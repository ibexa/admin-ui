(function (global, doc, ibexa) {
    const resizeEllipsisObserver = new ResizeObserver((entries) => {
        entries.forEach((entry) => {
            parseAll(entry.target);
        });
    });
    const parse = (baseElement = doc) => {
        if (!baseElement) {
            console.warn('No baseElement provided');

            return;
        }

        const middleEllipsisContainers = [...baseElement.querySelectorAll('.ibexa-middle-ellipsis')];

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
    // @deprecated, will be removed in 5.0
    const parseAll = () => parse(doc);
    const update = (baseElement, content) => {
        const contentElements = [...baseElement.querySelectorAll('.ibexa-middle-ellipsis__name-ellipsized')];
        const contentEscaped = ibexa.helpers.text.escapeHTML(content);

        baseElement.dataset.bsOriginalTitle = contentEscaped;
        contentElements.forEach((contentElement) => {
            contentElement.innerHTML = contentEscaped;
        });
        parseAll(baseElement);
    };

    ibexa.addConfig('helpers.ellipsis.middle', {
        parseAll,
        update,
    });
})(window, window.document, window.ibexa);
