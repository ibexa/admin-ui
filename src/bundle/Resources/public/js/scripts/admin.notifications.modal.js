(function (global, doc, ibexa, Translator, Routing) {
    let currentPageLink = null;
    let getNotificationsStatusErrorShowed = false;
    let lastFailedCountFetchNotificationNode = null;
    let selectedNotificationId = null;
    const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    const SELECTOR_MODAL_RESULTS = '.ibexa-notifications-modal__results .ibexa-scrollable-wrapper';
    const SELECTOR_MODAL_TITLE = '.ibexa-side-panel__header';
    const SELECTOR_LIST = '.ibexa-list--notifications';
    const CLASS_MODAL_LOADING = 'ibexa-notifications-modal--loading';
    const INTERVAL = 30000;
    const panel = doc.querySelector('.ibexa-notifications-modal');
    const { showErrorNotification, showWarningNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse, getTextFromResponse } = ibexa.helpers.request;
    const handleNotificationClickRequest = (notification, response) => {
        if (response.status === 'success') {
            notification.classList.add('ibexa-notifications-modal__item--read');
        }

        if (response.redirect) {
            global.location = response.redirect;
        }
    };
    const handleNotificationClick = (notification) => {
        const notificationReadLink = notification.dataset.notificationRead;
        const request = new Request(notificationReadLink, {
            mode: 'cors',
            credentials: 'same-origin',
        });

        fetch(request).then(getJsonFromResponse).then(handleNotificationClickRequest.bind(null, notification)).catch(showErrorNotification);
    };

    const getNotificationsStatus = () => {
        const notificationsTable = panel.querySelector(SELECTOR_LIST);
        const notificationsStatusLink = notificationsTable.dataset.notificationsCount;
        const request = new Request(notificationsStatusLink, {
            mode: 'cors',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return fetch(request)
            .then(getJsonFromResponse)
            .then((notificationsInfo) => {
                setPendingNotificationCount(notificationsInfo);
                updateModalTitleTotalInfo(notificationsInfo.total);
                getNotificationsStatusErrorShowed = false;
            })
            .catch(onGetNotificationsStatusFailure);
    };
    const onGetNotificationsStatusFailure = (error) => {
        if (lastFailedCountFetchNotificationNode && doc.contains(lastFailedCountFetchNotificationNode)) {
            return;
        }

        if (!getNotificationsStatusErrorShowed) {
            const message = Translator.trans(
                /* @Desc("Cannot update notifications") */ 'notifications.modal.message.error',
                { error: error.message },
                'ibexa_notifications',
            );

            showWarningNotification(message, (notificationNode) => {
                lastFailedCountFetchNotificationNode = notificationNode;
            });
        }

        getNotificationsStatusErrorShowed = true;
    };
    const updateModalTitleTotalInfo = (notificationsCount) => {
        const modalTitle = panel.querySelector(SELECTOR_MODAL_TITLE);
        const modalFooter = panel.querySelector('.ibexa-notifications-modal__view-all-btn--count');

        modalFooter.textContent = ` (${notificationsCount})`;
        modalTitle.dataset.notificationsTotal = `(${notificationsCount})`;

        if (notificationsCount < 10) {
            panel.querySelector('.ibexa-notifications-modal__count').textContent = `(${notificationsCount})`;
        }
    };
    const updatePendingNotificationsView = (notificationsInfo) => {
        const noticeDot = doc.querySelector('.ibexa-header-user-menu__notice-dot');

        noticeDot.dataset.count = notificationsInfo.pending;
        noticeDot.classList.toggle('ibexa-header-user-menu__notice-dot--no-notice', notificationsInfo.pending === 0);
    };
    const setPendingNotificationCount = (notificationsInfo) => {
        updatePendingNotificationsView(notificationsInfo);

        const notificationsTable = panel.querySelector(SELECTOR_LIST);
        const notificationsTotal = notificationsInfo.total;
        const notificationsTotalOld = parseInt(notificationsTable.dataset.notificationsTotal, 10);

        markAllAsReadBtn.disabled = notificationsInfo.pending === 0;

        if (notificationsTotal !== notificationsTotalOld) {
            notificationsTable.dataset.notificationsTotal = notificationsTotal;

            fetchNotificationPage(currentPageLink);
        }
    };
    const markAllAsRead = () => {
        const markAllAsReadLink = Routing.generate('ibexa.notifications.mark_all_as_read');
        const message = Translator.trans(
            /* @Desc("Cannot mark all notifications as read") */ 'notifications.modal.message.error.mark_all_as_read',
            {},
            'ibexa_notifications',
        );

        fetch(markAllAsReadLink, { mode: 'same-origin', credentials: 'same-origin' })
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    const allUnreadNotifications = doc.querySelectorAll('.ibexa-notifications-modal__item');

                    allUnreadNotifications.forEach((notification) => notification.classList.add('ibexa-notifications-modal__item--read'));
                    getNotificationsStatus();
                    const actions = doc.querySelectorAll('.ibexa-notifications-modal--mark-as-read');
                    const markAsUnreadLabel = Translator.trans(
                        /* @Desc("Mark as unread") */ 'notification.mark_as_unread',
                        {},
                        'ibexa_notifications',
                    );

                    actions.forEach((notification) => {
                        notification.classList.remove('ibexa-notifications-modal--mark-as-read');
                        notification.classList.add('ibexa-notifications-modal--mark-as-unread');
                        notification.textContent = markAsUnreadLabel;
                    });
                } else {
                    showErrorNotification(message);
                }
            })
            .catch(() => {
                showErrorNotification(message);
            });
    };
    const markAsRead = ({ currentTarget }) => {
        const { notificationId } = currentTarget.dataset;
        const markAsReadLink = Routing.generate('ibexa.notifications.mark_as_read', { notificationId });
        const message = Translator.trans(
            /* @Desc("Cannot mark notification as read") */ 'notifications.modal.message.error.mark_as_read',
            {},
            'ibexa_notifications',
        );

        fetch(markAsReadLink, { mode: 'same-origin', credentials: 'same-origin' })
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    const notification = doc.querySelector(`.ibexa-notifications-modal__item[data-notification-id="${notificationId}"]`);
                    const menuBranch = currentTarget.closest('.ibexa-multilevel-popup-menu__branch');
                    const menuInstance = ibexa.helpers.objectInstances.getInstance(menuBranch.menuInstanceElement);
                    const markAsUnreadLabel = Translator.trans(
                        /* @Desc("Mark as unread") */ 'notification.mark_as_unread',
                        {},
                        'ibexa_notifications',
                    );

                    menuInstance.closeMenu();
                    notification.classList.add('ibexa-notifications-modal__item--read');
                    currentTarget.classList.remove('ibexa-notifications-modal--mark-as-read');
                    currentTarget.classList.add('ibexa-notifications-modal--mark-as-unread');
                    currentTarget.textContent = markAsUnreadLabel;
                    getNotificationsStatus();
                } else {
                    showErrorNotification(message);
                }
            })
            .catch(() => {
                showErrorNotification(message);
            });
    };
    const markAsUnread = ({ currentTarget }) => {
        const { notificationId } = currentTarget.dataset;
        const markAsUnreadLink = Routing.generate('ibexa.notifications.mark_as_unread', { notificationId });
        const message = Translator.trans(
            /* @Desc("Cannot mark notification as unread") */ 'notifications.modal.message.error.mark_as_unread',
            {},
            'ibexa_notifications',
        );

        fetch(markAsUnreadLink, { mode: 'same-origin', credentials: 'same-origin' })
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    const notification = doc.querySelector(`.ibexa-notifications-modal__item[data-notification-id="${notificationId}"]`);
                    const menuBranch = currentTarget.closest('.ibexa-multilevel-popup-menu__branch');
                    const menuInstance = ibexa.helpers.objectInstances.getInstance(menuBranch.menuInstanceElement);
                    const markAsReadLabel = Translator.trans(
                        /* @Desc("Mark as read") */ 'notification.mark_as_read',
                        {},
                        'ibexa_notifications',
                    );

                    menuInstance.closeMenu();
                    notification.classList.remove('ibexa-notifications-modal__item--read');
                    currentTarget.classList.remove('ibexa-notifications-modal--mark-as-unread');
                    currentTarget.classList.add('ibexa-notifications-modal--mark-as-read');
                    currentTarget.textContent = markAsReadLabel;
                    getNotificationsStatus();
                } else {
                    showErrorNotification(message);
                }
            })
            .catch(() => {
                showErrorNotification(message);
            });
    };
    const handleMarkAsAction = ({ currentTarget }) => {
        const markAsReadLabel = Translator.trans(/* @Desc("Mark as read") */ 'notification.mark_as_read', {}, 'ibexa_notifications');

        currentTarget.textContent.trim() === markAsReadLabel ? markAsRead({ currentTarget }) : markAsUnread({ currentTarget });
    };
    const deleteNotification = () => {
        const deleteLink = Routing.generate('ibexa.notifications.delete', { notificationId: selectedNotificationId });
        const message = Translator.trans(
            /* @Desc("Cannot delete notification") */ 'notifications.modal.message.error.delete',
            {},
            'ibexa_notifications',
        );

        fetch(deleteLink, { method: 'POST', mode: 'same-origin', credentials: 'same-origin' })
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    const notification = doc.querySelector(`.ibexa-notifications-modal__item[data-notification-id="${selectedNotificationId}"]`);

                    notification.remove();
                    getNotificationsStatus();
                } else {
                    showErrorNotification(message);
                }
            })
            .catch(() => {
                showErrorNotification(message);
            });
    };
    const attachActionsListeners = () => {
        const attachListener = (node, callback) => node.addEventListener('click', callback, false);
        const markAsButtons = doc.querySelectorAll('.ibexa-notifications-modal--mark-as');
        const deleteButtons = doc.querySelectorAll('.ibexa-notifications-open-modal-button');
        const deleteButton = doc.querySelector('.ibexa-notifications-modal--delete--confirm');

        const setNotificationId = ({ currentTarget }) => {
            selectedNotificationId = currentTarget.dataset.notificationId;
        };

        markAsButtons.forEach((markAsButton) => {
            attachListener(markAsButton, handleMarkAsAction);
        });

        deleteButtons.forEach((deleteButton) => {
            attachListener(deleteButton, setNotificationId);
        });

        if (deleteButton) {
            deleteButton.addEventListener('click', deleteNotification);
        }
    };
    const showNotificationPage = (pageHtml) => {
        const modalResults = panel.querySelector(SELECTOR_MODAL_RESULTS);

        modalResults.innerHTML = pageHtml;
        toggleLoading(false);
        attachActionsListeners();

        doc.body.dispatchEvent(
            new CustomEvent('ibexa-multilevel-popup-menu:init', {
                detail: { container: modalResults },
            }),
        );
    };
    const toggleLoading = (show) => {
        panel.classList.toggle(CLASS_MODAL_LOADING, show);
    };
    const fetchNotificationPage = (link) => {
        if (!link) {
            return;
        }

        const request = new Request(link, {
            method: 'GET',
            headers: {
                Accept: 'text/html',
            },
            credentials: 'same-origin',
            mode: 'cors',
        });

        currentPageLink = link;
        toggleLoading(true);
        fetch(request).then(getTextFromResponse).then(showNotificationPage).catch(showErrorNotification);
    };
    const handleModalResultsClick = (event) => {
        const isActionBtn = event.target.closest('.ibexa-notifications-modal__actions');
        const notification = event.target.closest(SELECTOR_MODAL_ITEM);

        if (isActionBtn || !notification) {
            return;
        }

        handleNotificationClick(notification);
    };

    if (!panel) {
        return;
    }
    const markAllAsReadBtn = panel.querySelector('.ibexa-notifications-modal__mark-all-read-btn');
    const notificationsTable = panel.querySelector(SELECTOR_LIST);
    currentPageLink = notificationsTable.dataset.notifications;
    const interval = Number.parseInt(notificationsTable.dataset.notificationsCountInterval, 10) || INTERVAL;

    panel.querySelectorAll(SELECTOR_MODAL_RESULTS).forEach((link) => link.addEventListener('click', handleModalResultsClick, false));
    markAllAsReadBtn.addEventListener('click', markAllAsRead, false);

    const getNotificationsStatusLoop = () => {
        getNotificationsStatus().finally(() => {
            global.setTimeout(getNotificationsStatusLoop, interval);
        });
    };

    getNotificationsStatusLoop();
    attachActionsListeners();
})(window, window.document, window.ibexa, window.Translator, window.Routing);
