(function (global, doc) {
    'use strict';

    // Mirrors @ibexadesign/content/tab/versions/async_publication_status_badge.html.twig.
    // 'completed' has no backend counterpart and is faked here purely on the UI.
    const STATUS_BADGES = {
        queued: { variant: 'ibexa-badge--secondary', label: 'Queued' },
        processing: { variant: 'ibexa-badge--info', label: 'Publishing' },
        completed: { variant: 'ibexa-badge--success', label: 'Published' },
        failed: { variant: 'ibexa-badge--danger', label: 'Publish failed' },
    };

    function renderBadge(container, status) {
        const badgeDefinition = STATUS_BADGES[status];

        if (!badgeDefinition) {
            container.replaceChildren();

            return;
        }

        const badge = doc.createElement('span');

        badge.className = `ibexa-badge ibexa-badge--status ${badgeDefinition.variant}`;
        badge.textContent = badgeDefinition.label;

        container.replaceChildren(badge);
    }

    function init() {
        const containers = doc.querySelectorAll('.ibexa-async-publication-status');

        if (!containers.length) {
            return;
        }

        function registerWithMercure() {
            const mercureClient = global.ibexa && global.ibexa.mercureClient;

            if (mercureClient) {
                mercureClient.on('async_publication_status', () => {});

                return true;
            }

            return false;
        }

        if (!registerWithMercure()) {
            doc.body.addEventListener('ibexa-mercure:connected', registerWithMercure, { once: true });
        }

        doc.body.addEventListener('ibexa-mercure:async_publication_status', (event) => {
            const { contentId, versionNo, status } = event.detail;

            const selector = `.ibexa-async-publication-status[data-content-id="${contentId}"][data-version-no="${versionNo}"]`;

            doc.querySelectorAll(selector).forEach((container) => {
                renderBadge(container, status);
            });
        });
    }

    if (doc.readyState === 'loading') {
        doc.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
