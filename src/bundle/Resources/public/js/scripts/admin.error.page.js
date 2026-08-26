import { Alert } from '@ibexa-design-system/src/bundle/Resources/public/ts/components/alert';

(function (global, doc) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const escapeHTML = (string) => {
        const stringTempNode = doc.createElement('div');

        stringTempNode.appendChild(doc.createTextNode(string));

        return stringTempNode.innerHTML;
    };
    const getTemplate = (label) => {
        const templateName = `template${label.charAt(0).toUpperCase()}${label.slice(1)}`;

        return notificationsContainer.dataset[templateName] ?? notificationsContainer.dataset.templateInfo;
    };
    const addNotification = ({ detail }) => {
        const { label, message } = detail;
        const container = doc.createElement('div');
        const notification = getTemplate(label).replace('{{ message }}', escapeHTML(message)).replace('{{ icon_path }}', '');

        container.insertAdjacentHTML('beforeend', notification);

        const notificationNode = container.querySelector('.ids-alert');
        const alertInstance = new Alert(notificationNode);

        alertInstance.init();
        notificationsContainer.append(notificationNode);
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window, window.document);
