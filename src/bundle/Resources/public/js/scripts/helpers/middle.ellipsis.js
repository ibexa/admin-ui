import { parse as parseTooltips } from './tooltips.helper';
import { escapeHTML } from './text.helper';

const { document: doc } = window;
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
        parseTooltips(middleEllipsisContainer);

        resizeEllipsisObserver.observe(middleEllipsisContainer);
    });
};
const update = (baseElement, content) => {
    const contentElements = [...baseElement.querySelectorAll('.ibexa-middle-ellipsis__name-ellipsized')];
    const contentEscaped = escapeHTML(content);

    baseElement.dataset.bsOriginalTitle = contentEscaped;
    contentElements.forEach((contentElement) => {
        contentElement.innerHTML = contentEscaped;
    });
    parse(baseElement);
};

export { parse, update };
