const { bootstrap, document: doc } = window;

let lastInsertTooltipTarget = null;
const TOOLTIPS_SELECTOR = '[title], [data-tooltip-title]';
const observerConfig = {
    childList: true,
    subtree: true,
};
const resizeEllipsisObserver = new ResizeObserver((entries) => {
    entries.forEach((entry) => {
        parse(entry.target);
    });
});
const observer = new MutationObserver((mutationsList) => {
    if (lastInsertTooltipTarget) {
        mutationsList.forEach((mutation) => {
            const { addedNodes, removedNodes } = mutation;

            if (addedNodes.length) {
                addedNodes.forEach((addedNode) => {
                    if (addedNode instanceof Element) {
                        parse(addedNode);
                    }
                });
            }

            if (removedNodes.length) {
                removedNodes.forEach((removedNode) => {
                    if (removedNode.classList && !removedNode.classList.contains('ibexa-tooltip')) {
                        lastInsertTooltipTarget = null;
                        doc.querySelectorAll('.ibexa-tooltip.show').forEach((tooltipNode) => {
                            tooltipNode.remove();
                        });
                    }
                });
            }
        });
    }
});
const modifyPopperConfig = (iframe, defaultBsPopperConfig) => {
    if (!iframe) {
        return defaultBsPopperConfig;
    }

    const iframeDOMRect = iframe.getBoundingClientRect();
    const offsetX = iframeDOMRect.x;
    const offsetY = iframeDOMRect.y;
    const offsetModifier = {
        name: 'offset',
        options: {
            offset: ({ placement }) => {
                const [basePlacement] = placement.split('-');

                switch (basePlacement) {
                    case 'top':
                        return [offsetX, -offsetY];
                    case 'bottom':
                        return [offsetX, offsetY];
                    case 'right':
                        return [offsetY, offsetX];
                    case 'left':
                        return [offsetY, -offsetX];
                    default:
                        return [];
                }
            },
        },
    };
    const offsetModifierIndex = defaultBsPopperConfig.modifiers.findIndex((modifier) => modifier.name == 'offset');

    if (offsetModifierIndex != -1) {
        defaultBsPopperConfig.modifiers[offsetModifierIndex] = offsetModifier;
    } else {
        defaultBsPopperConfig.modifiers.push(offsetModifier);
    }

    return defaultBsPopperConfig;
};
const getTextHeight = (text, styles) => {
    const tag = doc.createElement('div');

    tag.innerHTML = text;

    for (const key in styles) {
        tag.style[key] = styles[key];
    }

    doc.body.appendChild(tag);

    const { height: texHeight } = tag.getBoundingClientRect();

    doc.body.removeChild(tag);

    return texHeight;
};
const isTitleEllipsized = (node) => {
    const title = node.dataset.originalTitle;
    const { width: nodeWidth, height: nodeHeight } = node.getBoundingClientRect();
    const computedNodeStyles = getComputedStyle(node);
    const styles = {
        width: `${nodeWidth}px`,
        padding: computedNodeStyles.getPropertyValue('padding'),
        'font-size': computedNodeStyles.getPropertyValue('font-size'),
        'font-family': computedNodeStyles.getPropertyValue('font-family'),
        'font-weight': computedNodeStyles.getPropertyValue('font-weight'),
        'font-style': computedNodeStyles.getPropertyValue('font-style'),
        'font-variant': computedNodeStyles.getPropertyValue('font-variant'),
        'line-height': computedNodeStyles.getPropertyValue('line-height'),
        'word-break': 'break-all',
    };

    const textHeight = getTextHeight(title, styles);

    return textHeight > nodeHeight;
};
const initializeTooltip = (tooltipNode) => {
    const { delayShow, delayHide } = tooltipNode.dataset;
    const delay = {
        show: delayShow ? parseInt(delayShow, 10) : 150,
        hide: delayHide ? parseInt(delayHide, 10) : 75,
    };
    const extraClass = tooltipNode.dataset.tooltipExtraClass ?? '';
    const placement = tooltipNode.dataset.tooltipPlacement ?? 'bottom';
    const trigger = tooltipNode.dataset.tooltipTrigger ?? 'hover';
    const useHtml = tooltipNode.dataset.tooltipUseHtml !== undefined;
    const container = tooltipNode.dataset.tooltipContainerSelector
        ? tooltipNode.closest(tooltipNode.dataset.tooltipContainerSelector)
        : 'body';
    const iframe = document.querySelector(tooltipNode.dataset.tooltipIframeSelector);

    new bootstrap.Tooltip(tooltipNode, {
        delay,
        placement,
        trigger,
        container,
        popperConfig: modifyPopperConfig.bind(null, iframe),
        html: useHtml,
        template: `<div class="tooltip ibexa-tooltip ${extraClass}">
                        <div class="tooltip-arrow ibexa-tooltip__arrow"></div>
                        <div class="tooltip-inner ibexa-tooltip__inner"></div>
                   </div>`,
    });

    tooltipNode.addEventListener('inserted.bs.tooltip', (event) => {
        lastInsertTooltipTarget = event.currentTarget;
    });
};
const parse = (baseElement = doc) => {
    if (!baseElement) {
        return;
    }

    const tooltipNodes = [...baseElement.querySelectorAll(TOOLTIPS_SELECTOR)];

    if (baseElement instanceof Element) {
        tooltipNodes.push(baseElement);
    }

    for (const tooltipNode of tooltipNodes) {
        const hasEllipsisStyle = getComputedStyle(tooltipNode).textOverflow === 'ellipsis';
        const hasNewTitle = tooltipNode.hasAttribute('title');
        const tooltipInitialized = !!tooltipNode.dataset.originalTitle;
        let shouldHaveTooltip = !hasEllipsisStyle;

        if (!tooltipInitialized && hasNewTitle) {
            resizeEllipsisObserver.observe(tooltipNode);
            tooltipNode.dataset.originalTitle = tooltipNode.title;

            if (!shouldHaveTooltip) {
                shouldHaveTooltip = isTitleEllipsized(tooltipNode);
            }

            if (shouldHaveTooltip) {
                initializeTooltip(tooltipNode);
            } else {
                tooltipNode.removeAttribute('title');
            }
        } else if (tooltipInitialized && (hasNewTitle || hasEllipsisStyle)) {
            if (hasNewTitle) {
                tooltipNode.dataset.originalTitle = tooltipNode.title;
            }
            const tooltipInstance = bootstrap.Tooltip.getInstance(tooltipNode);
            const hasTooltip = !!tooltipInstance;

            if (!shouldHaveTooltip) {
                shouldHaveTooltip = isTitleEllipsized(tooltipNode);
            }

            if (hasTooltip && ((hasNewTitle && shouldHaveTooltip) || !shouldHaveTooltip)) {
                tooltipInstance.dispose();
            }

            if (shouldHaveTooltip && (hasNewTitle || !hasTooltip)) {
                tooltipNode.title = tooltipNode.dataset.originalTitle;

                initializeTooltip(tooltipNode);
            } else {
                tooltipNode.removeAttribute('title');
            }
        }
    }
};
const hideAll = (baseElement = doc) => {
    if (!baseElement) {
        return;
    }

    const tooltipsNode = baseElement.querySelectorAll(TOOLTIPS_SELECTOR);

    for (const tooltipNode of tooltipsNode) {
        bootstrap.Tooltip.getOrCreateInstance(tooltipNode).hide();
    }
};
const observe = (baseElement = doc) => {
    observer.observe(baseElement, observerConfig);
};

export { parse, hideAll, observe };
