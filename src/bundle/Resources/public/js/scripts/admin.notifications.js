import { Alert } from '@ibexa-design-system/src/bundle/Resources/public/ts/components/alert';

import { appendNotification } from './helpers/notification.helper';

(function (global, doc, ibexa) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const behindModalContainer = doc.querySelector('.ibexa-notifications-container--behind-modal');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const moveNotificationsBehindModal = (event) => {
        if (event.defaultPrevented) {
            return;
        }

        behindModalContainer.append(...notificationsContainer.children);
    };
    const restoreNotifications = () => {
        if (doc.querySelector('.modal.show') || !behindModalContainer.childElementCount) {
            return;
        }

        notificationsContainer.prepend(...behindModalContainer.children);
    };
    const syncBehindModalOffset = ([entry]) => {
        behindModalContainer.style.setProperty('--ibexa-notifications-height', `${entry.borderBoxSize[0].blockSize}px`);
    };
    const restoreOnBackdropRemoval = (mutations) => {
        const isBackdropRemoved = mutations.some(({ removedNodes }) =>
            [...removedNodes].some((node) => node.classList?.contains('modal-backdrop')),
        );

        if (isBackdropRemoved) {
            restoreNotifications();
        }
    };
    const addNotification = ({ detail }) => {
        const { onShow, label, message, customIconPath = '' } = detail;
        const config = ibexa.adminUiConfig.notifications[label];
        const timeout = config ? config.timeout : 0;
        const notificationNode = appendNotification(notificationsContainer, {
            label,
            message,
            onShow: (node) => {
                if (customIconPath) {
                    node.querySelector('.ids-alert__icon use').setAttribute('xlink:href', customIconPath);
                }

                onShow?.(node);
            },
        });
        const alertInstance = new Alert(notificationNode);

        alertInstance.init();

        if (timeout) {
            global.setTimeout(() => alertInstance.dismiss(), timeout);
        }
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    const notificationsResizeObserver = new ResizeObserver(syncBehindModalOffset);
    const backdropMutationObserver = new MutationObserver(restoreOnBackdropRemoval);

    notificationsResizeObserver.observe(notificationsContainer);
    backdropMutationObserver.observe(doc.body, { childList: true });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
    doc.addEventListener('show.bs.modal', moveNotificationsBehindModal, false);
    doc.addEventListener('hidden.bs.modal', restoreNotifications, false);
})(window, window.document, window.ibexa);
