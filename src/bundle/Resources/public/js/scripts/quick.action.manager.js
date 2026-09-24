(function (global, doc) {
    const ACTION_BTN_VERTICAL_SPACING = 60.2;
    const ACTION_BTN_RIGHT_OFFSET = 28;
    const DEFAULT_EXTRA_BOTTOM_PADDING = 28;
    const isIframe = global.self !== global.top;
    let actionButtonConfigs = [];

    const handleQuickActionMenuVisibility = () => {
        if (!isIframe) {
            const quickActionMenu = doc.querySelector('.ibexa-quick-action-menu');

            if (quickActionMenu) {
                quickActionMenu.classList.remove('ibexa-quick-action-menu--hidden');
            }
        }
    };

    doc.addEventListener('DOMContentLoaded', handleQuickActionMenuVisibility, false);

    const registerButton = (config) => {
        if (!config || !config.container || actionButtonConfigs.some((btn) => btn.id === config.id)) {
            return;
        }

        actionButtonConfigs = [...actionButtonConfigs, config].sort((a, b) => a.priority - b.priority);
        recalculateButtonsLayout();
    };
    const unregisterButton = (id) => {
        actionButtonConfigs = actionButtonConfigs.filter((btn) => btn.id !== id);
        recalculateButtonsLayout();
    };
    const recalculateButtonsLayout = () => {
        const buttonsToRender = actionButtonConfigs.filter((btn) => {
            if (typeof btn.checkVisibility === 'function') {
                const isVisible = btn.checkVisibility();

                return isVisible;
            }

            return false;
        });

        const maxExtraPadding = Math.max(...buttonsToRender.map((config) => config.extraBottomPadding || DEFAULT_EXTRA_BOTTOM_PADDING));

        buttonsToRender.forEach((buttonConfig, index) => {
            const { container } = buttonConfig;

            if (!container.style.transition) {
                container.style.transition = 'all 0.3s ease-in-out';
            }

            container.style.position = 'fixed';
            container.style.right = `${ACTION_BTN_RIGHT_OFFSET}px`;
            container.style.zIndex = buttonConfig.zIndex || 1040;

            const bottomPosition = `${index * ACTION_BTN_VERTICAL_SPACING + maxExtraPadding}px`;

            container.style.bottom = bottomPosition;
        });
    };

    global.ibexa.quickAction = {
        registerButton,
        unregisterButton,
        recalculateButtonsLayout,
    };
})(window, window.document);
