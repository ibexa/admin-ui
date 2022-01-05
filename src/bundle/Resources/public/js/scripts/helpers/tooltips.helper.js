(function (global, doc, ibexa, bootstrap) {
    let lastInsertTooltipTarget = null;
    const TOOLTIPS_SELECTOR = '[title]';
    const observerConfig = {
        childList: true,
        subtree: true,
    };
    const observer = new MutationObserver((mutationsList) => {
        if (lastInsertTooltipTarget) {
            mutationsList.forEach((mutation) => {
                const { removedNodes } = mutation;

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
                    const basePlacement = placement.split('-')[0];

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

        for (tooltipNode of tooltipNodes) {
            if (tooltipNode.hasAttribute('title')) {
                const delay = {
                    show: parseInt(tooltipNode.dataset.delayShow, 10) ?? 150,
                    hide: parseInt(tooltipNode.dataset.delayHide, 10) ?? 75,
                };
                const extraClass = tooltipNode.dataset.tooltipExtraClass ?? '';
                const placement = tooltipNode.dataset.tooltipPlacement ?? 'bottom';
                const container = tooltipNode.dataset.tooltipContainerSelector
                    ? tooltipNode.closest(tooltipNode.dataset.tooltipContainerSelector)
                    : 'body';
                const iframe = document.querySelector(tooltipNode.dataset.tooltipIframeSelector);

                new bootstrap.Tooltip(tooltipNode, {
                    delay,
                    placement,
                    container,
                    popperConfig: modifyPopperConfig.bind(null, iframe),
                    html: true,
                    template: `<div class="tooltip ibexa-tooltip ${extraClass}">
                                    <div class="arrow ibexa-tooltip__arrow"></div>
                                    <div class="tooltip-inner ibexa-tooltip__inner"></div>
                               </div>`,
                });

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

        for (tooltipNode of tooltipsNode) {
            bootstrap.Tooltip.getOrCreateInstance(tooltipNode).hide();
        }
    };

    observer.observe(doc.querySelector('body'), observerConfig);

    ibexa.addConfig('helpers.tooltips', {
        parse,
        hideAll,
    });
})(window, window.document, window.ibexa, window.bootstrap);
