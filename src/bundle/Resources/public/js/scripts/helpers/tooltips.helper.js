(function (global, doc, ibexa, bootstrap) {
    let lastInsertTooltipTarget = null;
    const TOOLTIPS_SELECTOR = '[title]';
    const observerConfig = {
        childList: true,
        subtree: true,
    };
    const resizeEllipsisObserver = new ResizeObserver((entries) => {
        entries.forEach((entry) => {
            ibexa.helpers.tooltips.parse(entry.target);
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
    const parse = (baseElement = doc) => {
        if (!baseElement) {
            return;
        }

        const tooltipNodes = [...baseElement.querySelectorAll(TOOLTIPS_SELECTOR)];

        if (baseElement instanceof Element) {
            tooltipNodes.push(baseElement);
        }

        for (const tooltipNode of tooltipNodes) {
            if (tooltipNode.hasAttribute('title')) {
                const hasEllipsisStyle = getComputedStyle(tooltipNode).textOverflow === 'ellipsis';

                if (hasEllipsisStyle) {
                    resizeEllipsisObserver.observe(tooltipNode);

                    const isEllipsized = tooltipNode.scrollWidth > tooltipNode.offsetWidth;
                    const tooltipInstance = bootstrap.Tooltip.getInstance(tooltipNode);

                    if (tooltipInstance) {
                        if (!isEllipsized) {
                            tooltipInstance.dispose();
                        }

                        continue;
                    }

                    if (isEllipsized) {
                        if (tooltipNode.dataset.title) {
                            tooltipNode.title = tooltipNode.dataset.title;
                        }
                    } else {
                        continue;
                    }
                }

                const delay = {
                    show: parseInt(tooltipNode.dataset.delayShow, 10) ?? 150,
                    hide: parseInt(tooltipNode.dataset.delayHide, 10) ?? 75,
                };
                const extraClass = tooltipNode.dataset.tooltipExtraClass ?? '';
                const placement = tooltipNode.dataset.tooltipPlacement ?? 'bottom';
                const trigger = tooltipNode.dataset.tooltipTrigger ?? 'hover focus';
                const useHtml = tooltipNode.dataset.tooltipUseHtml !== undefined;
                const container = tooltipNode.dataset.tooltipContainerSelector
                    ? tooltipNode.closest(tooltipNode.dataset.tooltipContainerSelector)
                    : 'body';
                const iframe = document.querySelector(tooltipNode.dataset.tooltipIframeSelector);
                const tooltipInstance = bootstrap.Tooltip.getInstance(tooltipNode);

                if (tooltipInstance) {
                    tooltipNode.title = tooltipInstance._getTitle();

                    tooltipInstance.dispose();
                }

                tooltipNode.dataset.originalTitle = tooltipNode.title;

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

                tooltipNode.title = '';

                tooltipNode.addEventListener('inserted.bs.tooltip', (event) => {
                    lastInsertTooltipTarget = event.currentTarget;
                });
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
    const observe = (baseElement = doc.querySelector('body')) => {
        observer.observe(baseElement, observerConfig);
    }

    ibexa.addConfig('helpers.tooltips', {
        parse,
        hideAll,
        observe,
    });
})(window, window.document, window.ibexa, window.bootstrap);
