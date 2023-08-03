(function (global, doc, ibexa) {
    const resizeEllipsisObserver = new ResizeObserver((entries) => {
        entries.forEach((entry) => {
            parse(entry.target);
        });
    });
    const parse = (baseElement = doc) => {
        const isHTMLElement = baseElement instanceof Element || baseElement instanceof Document;

        if (!isHTMLElement) {
            console.warn('Provided element does not belong to Document interface');

            return;
        }

        const middleEllipsisContainers = [...baseElement.querySelectorAll('.ibexa-middle-ellipsis')];

        if (baseElement instanceof Element && baseElement.classList.contains('ibexa-middle-ellipsis')) {
            middleEllipsisContainers.push(baseElement);
        }

        middleEllipsisContainers.forEach((middleEllipsisContainer) => {
            const partStart = middleEllipsisContainer.querySelector('.ibexa-middle-ellipsis__name--start');
            const isEllipsized = partStart.scrollWidth > partStart.offsetWidth;

            if (!isEllipsized) {
                middleEllipsisContainer.dataset.bsOriginalTitle = '';
            } else {
                const partStartContentNode = partStart.querySelector('.ibexa-middle-ellipsis__name-ellipsized');

                middleEllipsisContainer.dataset.bsOriginalTitle = partStartContentNode.innerHTML;
            }

            middleEllipsisContainer.classList.toggle('ibexa-middle-ellipsis--ellipsized', isEllipsized);
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
        parse(baseElement);
    };

    ibexa.addConfig('helpers.ellipsis.middle', {
        parse,
        parseAll,
        update,
    });
})(window, window.document, window.ibexa);
