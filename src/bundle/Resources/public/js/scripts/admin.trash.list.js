(function (global, doc, ibexa, React, ReactDOM, Translator, flatpickr) {
    const CLASS_SORTED_ASC = 'ibexa-table__sort-column--asc';
    const CLASS_SORTED_DESC = 'ibexa-table__sort-column--desc';
    const CLASS_VISIBLE_DATE_RANGE = 'ibexa-trash-search-form__range-wrapper--visible';
    const sortedActiveField = doc.querySelector('#trash_search_sort_field').value;
    const sortedActiveDirection = doc.querySelector('#trash_search_sort_direction').value;
    const dateFields = doc.querySelectorAll('.ibexa-trash-search-form__range-select');
    const trashedTypeInput = doc.querySelector('#trash_search_trashed');
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const formSearch = doc.querySelector('form[name="trash_search"]');
    const sortField = doc.querySelector('#trash_search_sort_field');
    const sortDirection = doc.querySelector('#trash_search_sort_direction');
    const creatorInput = doc.querySelector('.ibexa-trash-search-form__item--creator .ibexa-trash-search-form__input');
    const usersList = doc.querySelector('.ibexa-trash-search-form__item--creator .ibexa-trash-search-form__user-list');
    const searchCreatorInput = doc.querySelector('#trash_search_creator');
    const sortableColumns = doc.querySelectorAll('.ibexa-table__sort-column');
    const btns = doc.querySelectorAll('.ibexa-btn--open-udw');
    const udwContainer = doc.getElementById('react-udw');
    const autoSendNodes = doc.querySelectorAll('.ibexa-trash-search-form__item--auto-send');
    const creatorFieldWrapper = doc.querySelector('.ibexa-trash-search-form__item--creator');
    const errorMessage = Translator.trans(/*@Desc("Cannot fetch user list")*/ 'trash.user_list.error', {}, 'trash_ui');
    const dateConfig = {
        mode: 'range',
        locale: {
            rangeSeparator: ' - ',
        },
        formatDate: (date) => ibexa.helpers.timezone.formatShortDateTime(date, null, ibexa.adminUiConfig.dateFormat.shortDate),
    };
    const closeUDW = () => ReactDOM.unmountComponentAtNode(udwContainer);
    const onConfirm = (form, content) => {
        const field = form.querySelector('#trash_item_restore_location_location');

        field.value = content.map((item) => item.id).join();

        closeUDW();
        form.submit();
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const form = event.target.closest('form[name="trash_item_restore"]');
        const config = JSON.parse(event.currentTarget.dataset.udwConfig);
        const title = Translator.trans(
            /*@Desc("Select a Location to restore the Content item(s)")*/ 'restore_under_new_location.title',
            {},
            'universal_discovery_widget',
        );

        ReactDOM.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: onConfirm.bind(this, form),
                onCancel,
                title,
                containersOnly: true,
                multiple: false,
                ...config,
            }),
            udwContainer,
        );
    };

    btns.forEach((btn) => btn.addEventListener('click', openUDW, false));

    const trashRestoreCheckboxes = [...doc.querySelectorAll('form[name="trash_item_restore"] input[type="checkbox"]')];
    const buttonRestore = doc.querySelector('#trash_item_restore_restore');
    const buttonRestoreUnderNewParent = doc.querySelector('#trash_item_restore_location_select_content');
    const buttonDelete = doc.querySelector('#delete-trash-items');

    const enableButtons = () => {
        const isEmptySelection = trashRestoreCheckboxes.every((el) => !el.checked);
        const isMissingParent = trashRestoreCheckboxes.some((el) => el.checked && parseInt(el.dataset.isParentInTrash, 10) === 1);

        if (buttonRestore) {
            buttonRestore.disabled = isEmptySelection || isMissingParent;
        }

        if (buttonDelete) {
            buttonDelete.disabled = isEmptySelection;
        }

        if (buttonRestoreUnderNewParent) {
            buttonRestoreUnderNewParent.disabled = isEmptySelection;
        }
    };
    const updateTrashForm = (checkboxes) => {
        checkboxes.forEach((checkbox) => {
            const trashFormCheckbox = doc.querySelector(`form[name="trash_item_delete"] input[type="checkbox"][value="${checkbox.value}"]`);

            if (trashFormCheckbox) {
                trashFormCheckbox.checked = checkbox.checked;
            }
        });
    };
    const handleCheckboxChange = (event) => {
        updateTrashForm([event.target]);
        enableButtons();
    };
    const getUsersList = (value, contentTypeIdentifiers) => {
        const body = JSON.stringify({
            ViewInput: {
                identifier: `find-user-by-name-${value}`,
                public: false,
                ContentQuery: {
                    FacetBuilders: {},
                    SortClauses: {},
                    Query: {
                        FullTextCriterion: `${value}*`,
                        ContentTypeIdentifierCriterion: contentTypeIdentifiers.split(','),
                    },
                    limit: 50,
                    offset: 0,
                },
            },
        });
        const request = new Request('/api/ibexa/v2/views', {
            method: 'POST',
            headers: {
                Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
                'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
                'X-Siteaccess': siteaccess,
                'X-CSRF-Token': token,
            },
            body,
            mode: 'same-origin',
            credentials: 'same-origin',
        });

        return fetch(request).then(ibexa.helpers.request.getJsonFromResponse);
    };
    const showUsersList = (instance, data) => {
        const hits = data.View.Result.searchHits.searchHit;

        const usersResponseList = hits.map((hit) => {
            const user = hit.value.Content;

            return {
                value: user._id,
                label: user.TranslatedName,
                selectedLabel: user.TranslatedName,
            };
        });

        if (usersResponseList.length) {
            instance.togglePopoverVisibility(true);

            instance.updatePopoverContent(usersResponseList);
        }
    };
    const initCreatorAutocompleteField = () => {
        const creatorWidget = new ibexa.core.AutocompleteInput({
            container: creatorFieldWrapper,
            getData: (instance, value) => {
                if (value.length >= 3) {
                    getUsersList(value, instance.sourceInput.dataset.contentTypeIdentifiers)
                        .then(showUsersList.bind(null, instance))
                        .catch(() => ibexa.helpers.notification.showErrorNotification(errorMessage));
                } else {
                    instance.togglePopoverVisibility(false);
                }
            },
            onChange: () => {
                formSearch.submit();
            },
        });

        creatorWidget.init();
    };
    const sortTrashItems = (event) => {
        const { target } = event;
        const { field, direction } = target.dataset;

        sortField.value = field;
        target.dataset.direction = direction === 'ASC' ? 'DESC' : 'ASC';
        sortDirection.setAttribute('value', direction === 'DESC' ? 1 : 0);
        formSearch.submit();
    };
    const toggleDatesSelectVisibility = (event) => {
        const datesRangeNode = doc.querySelector(event.target.dataset.targetSelector);

        if (event.target.value !== 'custom_range') {
            doc.querySelector(datesRangeNode.dataset.periodSelector).value = event.target.value;
            doc.querySelector(datesRangeNode.dataset.endSelector).value = '';
            datesRangeNode.classList.remove(CLASS_VISIBLE_DATE_RANGE);
            formSearch.submit();

            return;
        }

        datesRangeNode.classList.add(CLASS_VISIBLE_DATE_RANGE);
    };
    const setSelectedDateRange = (selectedDates, dateString, instance) => {
        const dateRange = instance.input.closest('.ibexa-trash-search-form__range-wrapper');

        if (selectedDates.length === 2) {
            const startDate = getUnixTimestampUTC(selectedDates[0]);
            const endDate = getUnixTimestampUTC(selectedDates[1]);
            const secondsInDay = 86400;
            const days = (endDate - startDate) / secondsInDay;

            doc.querySelector(dateRange.dataset.periodSelector).value = `P0Y0M${days}D`;
            doc.querySelector(dateRange.dataset.endSelector).value = endDate;

            formSearch.submit();
        }
    };
    const getUnixTimestampUTC = (dateObject) => {
        let date = new Date(Date.UTC(dateObject.getFullYear(), dateObject.getMonth(), dateObject.getDate()));
        date = Math.floor(date.getTime() / 1000);

        return date;
    };
    const initFlatPickr = (dateRangePickerNode) => {
        const { start, end } = dateRangePickerNode.dataset;
        const defaultDate = start && end ? [start, end] : [];

        flatpickr(dateRangePickerNode, {
            ...dateConfig,
            onChange: setSelectedDateRange,
            defaultDate,
        });
    };
    const handleAutoSubmitNodes = (event) => {
        event.preventDefault();

        if (event.target.value !== 'custom_range') {
            formSearch.submit();
        }
    };
    const setSortedClass = () => {
        doc.querySelectorAll('.ibexa-table__sort-column').forEach((node) => {
            node.classList.remove(CLASS_SORTED_ASC, CLASS_SORTED_DESC);
        });

        if (sortedActiveField) {
            const sortedFieldNode = doc.querySelector(`.ibexa-table__sort-column--${sortedActiveField}`);

            if (!sortedFieldNode) {
                return;
            }

            if (parseInt(sortedActiveDirection, 10) === 1) {
                sortedFieldNode.classList.add(CLASS_SORTED_ASC);
            } else {
                sortedFieldNode.classList.add(CLASS_SORTED_DESC);
            }
        }
    };

    initCreatorAutocompleteField();
    setSortedClass();
    dateFields.forEach(initFlatPickr);
    autoSendNodes.forEach((node) => node.addEventListener('change', handleAutoSubmitNodes, false));
    sortableColumns.forEach((column) => column.addEventListener('click', sortTrashItems, false));
    trashedTypeInput.addEventListener('change', toggleDatesSelectVisibility, false);
    updateTrashForm(trashRestoreCheckboxes);
    enableButtons();
    trashRestoreCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', handleCheckboxChange, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOM, window.Translator, window.flatpickr);
