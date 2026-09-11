import { Alert } from '@ibexa-design-system/src/bundle/Resources/public/ts/components/alert';

import { appendNotification } from './helpers/notification.helper';

(function (doc) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const addNotification = ({ detail }) => {
        const { label, message } = detail;
        const notificationNode = appendNotification(notificationsContainer, { label, message });

        new Alert(notificationNode).init();
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window.document);
