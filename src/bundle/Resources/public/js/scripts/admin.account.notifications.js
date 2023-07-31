(function (global, doc, ibexa, bootstrap) {
    // let currentPageLink = null;
    // let getNotificationsStatusErrorShowed = false;
    // let lastFailedCountFetchNotificationNode = null;
    // const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    // const SELECTOR_MODAL_RESULTS = '.ibexa-notifications-modal__results';
    // const SELECTOR_MODAL_TITLE = '.modal-title';
    // const SELECTOR_DESC_TEXT = '.description__text';
    // const SELECTOR_TABLE = '.ibexa-table--notifications';
    // const CLASS_ELLIPSIS = 'description__text--ellipsis';
    // const CLASS_PAGINATION_LINK = 'page-link';
    // const CLASS_MODAL_LOADING = 'ibexa-notifications-modal--loading';
    // const INTERVAL = 30000;
    // const notificationsPopup = doc.querySelector('.ibexa-notifications-popup');
    // const notificationsPopupTrigger = doc.querySelector('.ibexa-header-user-menu__notifications-toggler');
    // const notificationsPopupContainer = doc.querySelector('.ibexa-header-user-menu__notifications');
    // const { showErrorNotification, showWarningNotification } = ibexa.helpers.notification;
    // const { getJsonFromResponse, getTextFromResponse } = ibexa.helpers.request;
    // const markAsRead = (notification, response) => {
    //     if (response.status === 'success') {
    //         notification.classList.add('ibexa-notifications-modal__item--read');
    //     }
    //     if (response.redirect) {
    //         global.location = response.redirect;
    //     }
    // };
    // const handleNotificationClick = (notification) => {
    //     const notificationReadLink = notification.dataset.notificationRead;
    //     const request = new Request(notificationReadLink, {
    //         mode: 'cors',
    //         credentials: 'same-origin',
    //     });
    //     fetch(request).then(getJsonFromResponse).then(markAsRead.bind(null, notification)).catch(showErrorNotification);
    // };
    // const handleTableClick = (event) => {
    //     if (event.target.classList.contains('description__read-more')) {
    //         event.target.closest(SELECTOR_MODAL_ITEM).querySelector(SELECTOR_DESC_TEXT).classList.remove(CLASS_ELLIPSIS);
    //         return;
    //     }
    //     const notification = event.target.closest(SELECTOR_MODAL_ITEM);
    //     if (!notification) {
    //         return;
    //     }
    //     handleNotificationClick(notification);
    // };
    //
    //
    //
    //
    // const showNotificationPage = (pageHtml) => {
    //     const modalResults = notificationsPopup.querySelector(SELECTOR_MODAL_RESULTS);
    //     modalResults.innerHTML = pageHtml;
    //     toggleLoading(false);
    // };
    // const toggleLoading = (show) => {
    //     notificationsPopup.classList.toggle(CLASS_MODAL_LOADING, show);
    // };
    // const fetchNotificationPage = (link) => {
    //     if (!link) {
    //         return;
    //     }
    //     const request = new Request(link, {
    //         method: 'GET',
    //         headers: {
    //             Accept: 'text/html',
    //         },
    //         credentials: 'same-origin',
    //         mode: 'cors',
    //     });
    //     currentPageLink = link;
    //     toggleLoading(true);
    //     fetch(request).then(getTextFromResponse).then(showNotificationPage).catch(showErrorNotification);
    // };
    // const handleModalResultsClick = (event) => {
    //     const isPaginationBtn = event.target.classList.contains(CLASS_PAGINATION_LINK);
    //     if (isPaginationBtn) {
    //         handleNotificationsPageChange(event);
    //         return;
    //     }
    //     handleTableClick(event);
    // };
    // const handleNotificationsPageChange = (event) => {
    //     event.preventDefault();
    //     const notificationsPageLink = event.target.href;
    //     fetchNotificationPage(notificationsPageLink);
    // };
    // const notificationsTable = notificationsPopup.querySelector(SELECTOR_TABLE);
    // currentPageLink = notificationsTable.dataset.notifications;
    // notificationsPopup
    //     .querySelectorAll(SELECTOR_MODAL_RESULTS)
    //     .forEach((link) => link.addEventListener('click', handleModalResultsClick, false));
    // getNotificationsStatusLoop();
    // new bootstrap.Popover(notificationsPopupTrigger, {
    //     html: true,
    //     placement: 'bottom',
    //     content: notificationsPopup,
    //     container: notificationsPopupContainer,
    // });
    // const getNotificationsStatusLoop = () => {
    //     getNotificationsCount().finally(() => {
    //         global.setTimeout(getNotificationsStatusLoop, INTERVAL);
    //     });
    // };
    // getNotificationsStatusLoop();
    // let currentPageLink = null;
    // let getNotificationsStatusErrorShowed = false;
    // let lastFailedCountFetchNotificationNode = null;
    // const INTERVAL = 30000;
    // const SELECTOR_LIST_WRAPPER = '.ibexa-account-notifications';
    // const { showErrorNotification, showWarningNotification } = ibexa.helpers.notification;
    // const { getJsonFromResponse, getTextFromResponse } = ibexa.helpers.request;
    // const notificationsPopup = doc.querySelector('.ibexa-notifications-popup');
    // const notificationsPopupTrigger = doc.querySelector('.ibexa-header-user-menu__notifications-toggler');
    // const notificationsPopupContainer = doc.querySelector('.ibexa-header-user-menu__notifications');
    // const popup = new bootstrap.Popover(notificationsPopupTrigger, {
    //     html: true,
    //     placement: 'bottom',
    //     content: notificationsPopup,
    //     container: notificationsPopupContainer,
    // });
    // popup.show();
    // const getNotificationsStatus = () => {
    //     const notificationsListWrapper = notificationsPopup.querySelector(SELECTOR_LIST_WRAPPER);
    //     const { notificationsCountLink } = notificationsListWrapper.dataset;
    //     const notificationsCountRequest = new Request(notificationsCountLink, {
    //         mode: 'cors',
    //         credentials: 'same-origin',
    //         headers: {
    //             'X-Requested-With': 'XMLHttpRequest',
    //         },
    //     });
    //     return fetch(notificationsCountRequest)
    //         .then(getJsonFromResponse)
    //         .then((notificationsCount) => {
    //             setPendingNotificationCount(notificationsCount);
    //             getNotificationsStatusErrorShowed = false;
    //         })
    //         .catch(onGetNotificationsStatusFailure);
    // };
    // const setPendingNotificationCount = (notificationsCount) => {
    //     const noticeDot = doc.querySelector('.ibexa-header-user-menu__notice-dot');
    //     const { notificationstTotal: listWrapperNotificationsTotalCount } = notificationsListWrapper.dataset;
    //     const { total: requestNotificationsTotalCount } = notificationsCount;
    //     console.log(listWrapperNotificationsTotalCount, requestNotificationsTotalCount);
    //     // const notificationsListWrapper = notificationsPopup.querySelector(SELECTOR_LIST_WRAPPER);
    //     // const { notificationstTotal: listWrapperNotificationsTotalCount } = notificationsListWrapper.dataset;
    //     // const { total: requestNotificationsTotalCount } = notificationsCount;
    //     // const notificationsTotalOld = parseInt(notificationsTable.dataset.notificationsTotal, 10);
    //     noticeDot.innerText = notificationsCount.pending;
    //     noticeDot.classList.toggle('ibexa-header-user-menu__notice-dot--no-notice', notificationsCount.pending === 0);
    //     // if (parseInt(listWrapperNotificationsTotalCount, 10) !== parseInt(requestNotificationsTotalCount, 10)) {
    //     //     notificationsListWrapper.dataset.notificationsTotal = requestNotificationsTotalCount;
    //     //     fetchNotificationPage(currentPageLink);
    //     // }
    //     // updatePendingNotificationsView(notificationsInfo);
    // };
    // const onGetNotificationsStatusFailure = (error) => {
    //     if (lastFailedCountFetchNotificationNode && doc.contains(lastFailedCountFetchNotificationNode)) {
    //         return;
    //     }
    //     if (!getNotificationsStatusErrorShowed) {
    //         const message = Translator.trans(
    //             /* @Desc("Cannot update notifications") */ 'notifications.modal.message.error',
    //             { error: error.message },
    //             'notifications',
    //         );
    //         showWarningNotification(message, (notificationNode) => {
    //             lastFailedCountFetchNotificationNode = notificationNode;
    //         });
    //     }
    //     getNotificationsStatusErrorShowed = true;
    // };
    // const updatePendingNotificationsView = (notificationsInfo) => {
    //     const noticeDot = doc.querySelector('.ibexa-header-user-menu__notice-dot');
    //     noticeDot.innerText = notificationsInfo.pending;
    //     noticeDot.classList.toggle('ibexa-header-user-menu__notice-dot--no-notice', notificationsInfo.pending === 0);
    // };
    // const getNotificationsStatusLoop = () => {
    //     getNotificationsStatus().finally(() => {
    //         global.setTimeout(getNotificationsStatusLoop, INTERVAL);
    //     });
    // };
    // getNotificationsStatusLoop();

    let updateCounterErrorShowed = false;
    let currentPageLink = '/admin/notifications/render/page/1';
    const UPDATE_LOOP_INTERVAL = 5000;
    const SELECTOR_NOTIFICATIONS_LIST_WRAPPER = '.ibexa-account-notifications';
    const NOTIFICATIONS_COUNT_ENDPOINT = '/admin/notifications/count';
    const updateCounterWarningMsg = Translator.trans(
        /* @Desc("Cannot update notifications") */ 'counter.update.warning',
        {},
        'notifications',
    );
    const updateListErrorMsg = Translator.trans(/* @Desc("Cannot update notifications list") */ 'list.update.error', {}, 'notifications');
    const { showErrorNotification, showWarningNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse, getTextFromResponse } = ibexa.helpers.request;
    const notificationsPopupContent = doc.querySelector('.ibexa-notifications-popup');

    if (!notificationsPopupContent) {
        return;
    }

    const notificationsPopupBody = notificationsPopupContent.querySelector('.ibexa-notifications-popup__body');
    const notificationsPopupTrigger = doc.querySelector('.ibexa-header-user-menu__notifications-toggler');
    const notificationsPopupContainer = doc.querySelector('.ibexa-header-user-menu__notifications');
    const toggleLoader = () => {
        notificationsPopupContent.classList.toggle('ibexa-notifications-popup--is-loading');
    };
    const attachEvents = () => {
        const paginationLinks = notificationsPopupContent.querySelectorAll('.page-link[href]');

        [...paginationLinks].forEach((paginationLink) =>
            paginationLink.addEventListener('click', (event) => {
                event.preventDefault();

                const pageLink = event.target.href;

                updateList(pageLink);
            }),
        );
    };
    const markAsRead = () => {
        // Mark as read
        // if (response.status === 'success') {
        //     notification.classList.add('ibexa-notifications-modal__item--read');
        // }
        // if (response.redirect) {
        //     global.location = response.redirect;
        // }
        //handle click mark as read
        //     const notificationReadLink = notification.dataset.notificationRead;
        //     const request = new Request(notificationReadLink, {
        //         mode: 'cors',
        //         credentials: 'same-origin',
        //     });
        //     fetch(request).then(getJsonFromResponse).then(markAsRead.bind(null, notification)).catch(showErrorNotification);
    };
    const updateNotifications = async () => {
        const notificationsListWrapper = notificationsPopupContent.querySelector(SELECTOR_NOTIFICATIONS_LIST_WRAPPER);
        const { notificationsTotal: totalCount } = notificationsListWrapper.dataset;

        await updateCounters();

        const { notificationsTotal: updatedTotalCount } = notificationsListWrapper.dataset;

        if (parseInt(totalCount) !== parseInt(updatedTotalCount)) {
            await updateList(currentPageLink);
        }
    };
    const updateCounters = () => {
        const notificationsCountRequest = new Request(NOTIFICATIONS_COUNT_ENDPOINT, {
            mode: 'cors',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return fetch(notificationsCountRequest)
            .then(getJsonFromResponse)
            .then((notificationsCount) => {
                const noticeDot = doc.querySelector('.ibexa-header-user-menu__notice-dot');
                const notificationsListWrapper = notificationsPopupContent.querySelector(SELECTOR_NOTIFICATIONS_LIST_WRAPPER);
                const { pending, total } = notificationsCount;

                noticeDot.innerText = pending;
                noticeDot.classList.toggle('ibexa-header-user-menu__notice-dot--no-notice', pending === 0);
                notificationsListWrapper.dataset.notificationsTotal = total;
                updateCounterErrorShowed = false;
            })
            .catch(() => {
                if (updateCounterErrorShowed) {
                    return;
                }

                showWarningNotification(updateCounterWarningMsg);
                updateCounterErrorShowed = true;
            });
    };
    const updateList = (pageLink) => {
        const fetchNotificationsPageRequest = new Request(pageLink, {
            method: 'GET',
            headers: {
                Accept: 'text/html',
            },
            credentials: 'same-origin',
            mode: 'cors',
        });

        currentPageLink = pageLink;
        toggleLoader();

        return fetch(fetchNotificationsPageRequest)
            .then(getTextFromResponse)
            .then((listContent) => {
                notificationsPopupBody.innerHTML = listContent;
                attachEvents();
                toggleLoader();
            })
            .catch(() => {
                showErrorNotification(updateListErrorMsg);
                toggleLoader();
            });
    };
    const notificationsUpdateLoop = () => {
        updateNotifications().finally(() => {
            global.setTimeout(notificationsUpdateLoop, UPDATE_LOOP_INTERVAL);
        });
    };

    const popup = new bootstrap.Popover(notificationsPopupTrigger, {
        html: true,
        placement: 'bottom',
        content: notificationsPopupContent,
        container: notificationsPopupContainer,
    });
    // popup.show();

    attachEvents();
    notificationsUpdateLoop();
})(window, window.document, window.ibexa, window.bootstrap);
