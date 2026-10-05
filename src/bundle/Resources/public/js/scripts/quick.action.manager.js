(function (global, doc, ibexa) {
    const { calculateRem } = ibexa.helpers.css;
    const ACTION_BTN_VERTICAL_SPACING = 70;
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

        const maxExtraPadding = Math.max(...buttonsToRender.map((config) => config.extraBottomPadding || 32));

        buttonsToRender.forEach((buttonConfig, index) => {
            const { container } = buttonConfig;

            if (!container.style.transition) {
                container.style.transition = 'all 0.3s ease-in-out';
            }

            container.style.position = 'fixed';
            container.style.right = calculateRem(32);
            container.style.zIndex = buttonConfig.zIndex || 1040;

            container.style.bottom = calculateRem(index * ACTION_BTN_VERTICAL_SPACING + maxExtraPadding);
        });
    };

    global.ibexa.quickAction = {
        registerButton,
        unregisterButton,
        recalculateButtonsLayout,
    };
})(window, window.document, window.ibexa);
