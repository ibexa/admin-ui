import { Alert } from '@ibexa-design-system/src/bundle/Resources/public/ts/components/alert';

import { appendNotification } from './helpers/notification.helper';

(function (global, doc, ibexa) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const addNotification = ({ detail }) => {
        const { onShow, label, message, customIconPath = '' } = detail;
        const config = ibexa.adminUiConfig.notifications[label];
        const timeout = config ? config.timeout : 0;
        const notificationNode = appendNotification(notificationsContainer, {
            label,
            message,
            iconPath: customIconPath,
            onShow,
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

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window, window.document, window.ibexa);
