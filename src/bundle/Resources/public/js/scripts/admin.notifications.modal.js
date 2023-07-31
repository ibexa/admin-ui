(function (global, doc, ibexa, Translator) {
    let currentPageLink = null;
    let getNotificationsStatusErrorShowed = false;
    let lastFailedCountFetchNotificationNode = null;
    const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    const SELECTOR_MODAL_RESULTS = '.ibexa-notifications-modal__results';
    const SELECTOR_MODAL_TITLE = '.modal-title';
    const SELECTOR_DESC_TEXT = '.description__text';
    const SELECTOR_TABLE = '.ibexa-table--notifications';
    const CLASS_ELLIPSIS = 'description__text--ellipsis';
    const CLASS_PAGINATION_LINK = 'page-link';
    const CLASS_MODAL_LOADING = 'ibexa-notifications-modal--loading';
    const INTERVAL = 30000;
    const modal = doc.querySelector('.ibexa-notifications-modal');
    const { showErrorNotification, showWarningNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse, getTextFromResponse } = ibexa.helpers.request;
    const markAsRead = (notification, response) => {
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

        fetch(request).then(getJsonFromResponse).then(markAsRead.bind(null, notification)).catch(showErrorNotification);
    };
    const handleTableClick = (event) => {
        if (event.target.classList.contains('description__read-more')) {
            event.target.closest(SELECTOR_MODAL_ITEM).querySelector(SELECTOR_DESC_TEXT).classList.remove(CLASS_ELLIPSIS);

            return;
        }

        const notification = event.target.closest(SELECTOR_MODAL_ITEM);

        if (!notification) {
            return;
        }

        handleNotificationClick(notification);
    };
    const getNotificationsStatus = () => {
        const notificationsTable = modal.querySelector(SELECTOR_TABLE);
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

    /**
     * Handle a failure while getting notifications status
     *
     * @method onGetNotificationsStatusFailure
     */
    const onGetNotificationsStatusFailure = (error) => {
        if (lastFailedCountFetchNotificationNode && doc.contains(lastFailedCountFetchNotificationNode)) {
            return;
        }

        if (!getNotificationsStatusErrorShowed) {
            const message = Translator.trans(
                /* @Desc("Cannot update notifications") */ 'notifications.modal.message.error',
                { error: error.message },
                'notifications',
            );

            showWarningNotification(message, (notificationNode) => {
                lastFailedCountFetchNotificationNode = notificationNode;
            });
        }

        getNotificationsStatusErrorShowed = true;
    };
    const updateModalTitleTotalInfo = (notificationsCount) => {
        const modalTitle = modal.querySelector(SELECTOR_MODAL_TITLE);

        modalTitle.dataset.notificationsTotal = `(${notificationsCount})`;
    };
    const updatePendingNotificationsView = (notificationsInfo) => {
        const noticeDot = doc.querySelector('.ibexa-header-user-menu__notice-dot');

        noticeDot.innerText = notificationsInfo.pending;
        noticeDot.classList.toggle('ibexa-header-user-menu__notice-dot--no-notice', notificationsInfo.pending === 0);

    };
    const setPendingNotificationCount = (notificationsInfo) => {
        updatePendingNotificationsView(notificationsInfo);

        const notificationsTable = modal.querySelector(SELECTOR_TABLE);
        const notificationsTotal = notificationsInfo.total;
        const notificationsTotalOld = parseInt(notificationsTable.dataset.notificationsTotal, 10);

        if (notificationsTotal !== notificationsTotalOld) {
            notificationsTable.dataset.notificationsTotal = notificationsTotal;

            fetchNotificationPage(currentPageLink);
        }
    };
    const showNotificationPage = (pageHtml) => {
        const modalResults = modal.querySelector(SELECTOR_MODAL_RESULTS);

        modalResults.innerHTML = pageHtml;
        toggleLoading(false);
    };
    const toggleLoading = (show) => {
        modal.classList.toggle(CLASS_MODAL_LOADING, show);
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
        const isPaginationBtn = event.target.classList.contains(CLASS_PAGINATION_LINK);

        if (isPaginationBtn) {
            handleNotificationsPageChange(event);
            return;
        }

        handleTableClick(event);
    };
    const handleNotificationsPageChange = (event) => {
        event.preventDefault();

        const notificationsPageLink = event.target.href;

        fetchNotificationPage(notificationsPageLink);
    };

    if (!modal) {
        return;
    }

    const notificationsTable = modal.querySelector(SELECTOR_TABLE);
    currentPageLink = notificationsTable.dataset.notifications;

    modal.querySelectorAll(SELECTOR_MODAL_RESULTS).forEach((link) => link.addEventListener('click', handleModalResultsClick, false));

    const getNotificationsStatusLoop = () => {
        getNotificationsStatus().finally(() => {
            global.setTimeout(getNotificationsStatusLoop, INTERVAL);
        });
    };

    getNotificationsStatusLoop();
})(window, window.document, window.ibexa, window.Translator);
