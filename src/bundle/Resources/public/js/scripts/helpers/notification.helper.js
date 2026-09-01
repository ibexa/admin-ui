import { Alert } from '@ibexa-design-system/src/bundle/Resources/public/ts/components/alert';

import { getRootDOMElement } from './context.helper';
import { escapeHTML } from './text.helper';

const NOTIFICATION_INFO_LABEL = 'info';
const NOTIFICATION_SUCCESS_LABEL = 'success';
const NOTIFICATION_WARNING_LABEL = 'warning';
const NOTIFICATION_ERROR_LABEL = 'error';

/**
 * Returns the notification template rendered for a given label
 *
 * @function getNotificationTemplate
 * @param {HTMLElement} container notifications container
 * @param {String} label
 * @returns {String}
 */
const getNotificationTemplate = (container, label) => {
    const templateName = `template${label.charAt(0).toUpperCase()}${label.slice(1)}`;

    return container.dataset[templateName] ?? container.dataset.templateInfo;
};

/**
 * Renders a notification from the container's template, initializes it and appends it to the container
 *
 * @function appendNotification
 * @param {HTMLElement} container notifications container
 * @param {Object} config
 * @param {String} config.label
 * @param {String} config.message message to escape and render
 * @param {String} [config.iconPath] custom icon path
 * @param {Object} [config.rawPlaceholdersMap] `{{ placeholder }}` occurrences to fill with raw HTML
 * @returns {Object} appended notification node and its Alert instance
 */
const appendNotification = (container, { label, message, iconPath = '', rawPlaceholdersMap = {} }) => {
    const wrapper = document.createElement('div');
    let finalMessage = escapeHTML(message);

    Object.entries(rawPlaceholdersMap).forEach(([placeholder, rawText]) => {
        finalMessage = finalMessage.replace(`{{ ${placeholder} }}`, rawText);
    });

    const notification = getNotificationTemplate(container, label)
        .replace('{{ message }}', finalMessage)
        .replace('{{ icon_path }}', iconPath);

    wrapper.insertAdjacentHTML('beforeend', notification);

    const notificationNode = wrapper.querySelector('.ids-alert');
    const alertInstance = new Alert(notificationNode);

    alertInstance.init();
    container.append(notificationNode);

    return { notificationNode, alertInstance };
};

/**
 * Dispatches notification event
 *
 * @function showNotification
 * @param {Object} detail
 * @param {String} detail.message
 * @param {String} detail.label
 * @param {Function} [detail.onShow] to be called after notification Node was added
 * @param {Object} detail.rawPlaceholdersMap
 */
const showNotification = (detail) => {
    const rootDOMElement = getRootDOMElement();
    const event = new CustomEvent('ibexa-notify', { detail });

    rootDOMElement.dispatchEvent(event);
};

/**
 * Dispatches info notification event
 *
 * @function showInfoNotification
 * @param {String} message
 * @param {Function} [onShow] to be called after notification Node was added
 * @param {Object} rawPlaceholdersMap
 */
const showInfoNotification = (message, onShow, rawPlaceholdersMap = {}) =>
    showNotification({
        message,
        label: NOTIFICATION_INFO_LABEL,
        onShow,
        rawPlaceholdersMap,
    });

/**
 * Dispatches success notification event
 *
 * @function showSuccessNotification
 * @param {String} message
 * @param {Function} [onShow] to be called after notification Node was added
 * @param {Object} rawPlaceholdersMap
 */
const showSuccessNotification = (message, onShow, rawPlaceholdersMap = {}) =>
    showNotification({
        message,
        label: NOTIFICATION_SUCCESS_LABEL,
        onShow,
        rawPlaceholdersMap,
    });

/**
 * Dispatches warning notification event
 *
 * @function showWarningNotification
 * @param {String} message
 * @param {Function} [onShow] to be called after notification Node was added
 * @param {Object} rawPlaceholdersMap
 */
const showWarningNotification = (message, onShow, rawPlaceholdersMap = {}) =>
    showNotification({
        message,
        label: NOTIFICATION_WARNING_LABEL,
        onShow,
        rawPlaceholdersMap,
    });

/**
 * Dispatches error notification event
 *
 * @function showErrorNotification
 * @param {(string | Error)} error
 * @param {Function} [onShow] to be called after notification Node was added
 * @param {Object} rawPlaceholdersMap
 */
const showErrorNotification = (error, onShow, rawPlaceholdersMap = {}) => {
    const isErrorObj = error instanceof Error;
    const message = isErrorObj ? error.message : error;

    showNotification({
        message,
        label: NOTIFICATION_ERROR_LABEL,
        onShow,
        rawPlaceholdersMap,
    });
};

export {
    appendNotification,
    showNotification,
    showInfoNotification,
    showSuccessNotification,
    showWarningNotification,
    showErrorNotification,
};
