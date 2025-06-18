(function (global, doc, ibexa, flatpickr, React, ReactDOM) {
    const { escapeHTML, escapeHTMLAttribute } = ibexa.helpers.text;
    const { dangerouslySetInnerHTML } = ibexa.helpers.dom;
    const { getInstance } = ibexa.helpers.objectInstances;
    let getUsersTimeout;
    const SELECTOR_TAG = '.ibexa-tag';
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const filters = doc.querySelector('.ibexa-filters');
    const clearBtn = filters.querySelector('.ibexa-btn--clear');
    const applyBtn = filters.querySelector('.ibexa-btn--apply');
    const contentTypeSelect = doc.querySelector('.ibexa-filters__item--content-type .ibexa-filters__select');
    const sectionSelect = doc.querySelector('.ibexa-filters__item--section .ibexa-filters__select');
    const lastModifiedSelectNode = doc.querySelector('.ibexa-filters__item--modified .ibexa-filters__select');
    const lastModifiedSelect = getInstance(lastModifiedSelectNode);
    const lastModifiedDateRangeNode = doc.querySelector('.ibexa-filters__item--modified .ibexa-date-time-range-single');
    const lastModifiedDateRange = getInstance(lastModifiedDateRangeNode);
    const lastCreatedSelectNode = doc.querySelector('.ibexa-filters__item--created .ibexa-filters__select');
    const lastCreatedSelect = getInstance(lastCreatedSelectNode);
    const lastCreatedDateRangeNode = doc.querySelector('.ibexa-filters__item--created .ibexa-date-time-range-single');
    const lastCreatedDateRange = getInstance(lastCreatedDateRangeNode);
    const creatorInput = doc.querySelector('.ibexa-filters__item--creator .ibexa-input');
    const searchCreatorInput = doc.querySelector('#search_creator');
    const usersList = doc.querySelector('.ibexa-filters__item--creator .ibexa-filters__user-list');
    const contentTypeCheckboxes = doc.querySelectorAll('.ibexa-content-type-selector__item [type="checkbox"]');
    const selectSubtreeBtn = doc.querySelector('.ibexa-filters__item--subtree .ibexa-tag-view-select__btn-select-path');
    const subtreeInput = doc.querySelector('#search_subtree');
    const showMoreBtns = doc.querySelectorAll('.ibexa-content-type-selector__show-more');
    const clearFilters = (event) => {
        event.preventDefault();

        const option = contentTypeSelect.querySelector('option');
        const defaultText = option.dataset.default;
        const lastModifiedDataRange = doc.querySelector(lastModifiedSelectNode.dataset.targetSelector);
        const lastCreatedDataRange = doc.querySelector(lastCreatedSelectNode.dataset.targetSelector);
        const lastModifiedPeriod = doc.querySelector(lastModifiedDataRange.dataset.periodSelector);
        const lastModifiedEnd = doc.querySelector(lastModifiedDataRange.dataset.endSelector);
        const lastCreatedPeriod = doc.querySelector(lastCreatedDataRange.dataset.periodSelector);
        const lastCreatedEnd = doc.querySelector(lastCreatedDataRange.dataset.endSelector);

        option.innerHTML = defaultText;
        contentTypeCheckboxes.forEach((checkbox) => {
            checkbox.removeAttribute('checked');
            checkbox.checked = false;
        });

        if (sectionSelect) {
            sectionSelect[0].selected = true;
        }

        lastModifiedSelectNode[0].selected = true;
        lastCreatedSelectNode[0].selected = true;
        lastModifiedSelectNode.querySelector('option').selected = true;
        lastModifiedPeriod.value = '';
        lastModifiedEnd.value = '';
        lastCreatedPeriod.value = '';
        lastCreatedEnd.value = '';
        subtreeInput.value = '';

        handleResetUser();

        event.target.closest('form').submit();
    };
    const toggleDisabledStateOnApplyBtn = () => {
        const contentTypeOption = contentTypeSelect.querySelector('option');
        const isContentTypeSelected = contentTypeOption.innerHTML !== contentTypeOption.dataset.default;
        const isSectionSelected = sectionSelect ? !!sectionSelect.value : false;
        const isCreatorSelected = !!searchCreatorInput.value;
        const isSubtreeSelected = !!subtreeInput.value.trim().length;
        let isModifiedSelected = !!lastModifiedSelectNode.value;
        let isCreatedSelected = !!lastCreatedSelectNode.value;

        if (lastModifiedSelectNode.value === 'custom_range') {
            const { periodSelector, endSelector } = lastModifiedDateRangeNode.dataset;
            const lastModifiedPeriodValue = doc.querySelector(periodSelector).value;
            const lastModifiedEndDate = doc.querySelector(endSelector).value;

            if (!lastModifiedPeriodValue || !lastModifiedEndDate) {
                isModifiedSelected = false;
            }
        }

        if (lastCreatedSelectNode.value === 'custom_range') {
            const { periodSelector, endSelector } = lastCreatedDateRangeNode.dataset;
            const lastCreatedPeriodValue = doc.querySelector(periodSelector).value;
            const lastCreatedEndDate = doc.querySelector(endSelector).value;

            if (!lastCreatedPeriodValue || !lastCreatedEndDate) {
                isCreatedSelected = false;
            }
        }

        const isEnabled =
            isContentTypeSelected || isSectionSelected || isModifiedSelected || isCreatedSelected || isCreatorSelected || isSubtreeSelected;
        const methodName = isEnabled ? 'removeAttribute' : 'setAttribute';

        applyBtn[methodName]('disabled', !isEnabled);
    };
    const toggleDatesSelectVisibility = (select, dateRange) => {
        if (select.value !== 'custom_range') {
            dateRange.clearDates();
            dateRange.toggleHidden(true);

            toggleDisabledStateOnApplyBtn();

            return;
        }

        dateRange.toggleHidden(false);
    };
    const filterByContentType = () => {
        const selectedCheckboxes = [...contentTypeCheckboxes].filter((checkbox) => checkbox.checked);
        const contentTypesText = selectedCheckboxes.map((checkbox) => escapeHTML(checkbox.dataset.name)).join(', ');
        const [option] = contentTypeSelect;
        const defaultText = option.dataset.default;

        dangerouslySetInnerHTML(option, contentTypesText || defaultText);

        toggleDisabledStateOnApplyBtn();
    };
    const getUsersList = (value) => {
        const body = JSON.stringify({
            ViewInput: {
                identifier: `find-user-by-name-${value}`,
                public: false,
                ContentQuery: {
                    FacetBuilders: {},
                    SortClauses: {},
                    Query: {
                        FullTextCriterion: `${value}*`,
                        ContentTypeIdentifierCriterion: creatorInput.dataset.contentTypeIdentifiers.split(','),
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

        fetch(request)
            .then((response) => response.json())
            .then(showUsersList);
    };
    const createUsersListItem = (user) => {
        const userNameHtmlEscaped = escapeHTML(user.TranslatedName);
        const userNameHtmlAttributeEscaped = escapeHTMLAttribute(user.TranslatedName);

        return `<li data-id="${user._id}" data-name="${userNameHtmlAttributeEscaped}" class="ibexa-filters__user-item">${userNameHtmlEscaped}</li>`;
    };
    const showUsersList = (data) => {
        const hits = data.View.Result.searchHits.searchHit;
        const users = hits.reduce((total, hit) => total + createUsersListItem(hit.value.Content), '');
        const methodName = users ? 'addEventListener' : 'removeEventListener';

        dangerouslySetInnerHTML(usersList, users);
        usersList.classList.remove('ibexa-filters__user-list--hidden');

        doc.querySelector('body')[methodName]('click', handleClickOutsideUserList, false);
    };
    const handleTyping = (event) => {
        const value = event.target.value.trim();

        window.clearTimeout(getUsersTimeout);

        if (value.length > 2) {
            getUsersTimeout = window.setTimeout(getUsersList.bind(null, value), 200);
        } else {
            usersList.classList.add('ibexa-filters__user-list--hidden');
            doc.querySelector('body').removeEventListener('click', handleClickOutsideUserList, false);
        }
    };
    const handleSelectUser = (event) => {
        searchCreatorInput.value = event.target.dataset.id;

        usersList.classList.add('ibexa-filters__user-list--hidden');

        creatorInput.value = event.target.dataset.name;
        creatorInput.setAttribute('disabled', true);

        doc.querySelector('body').removeEventListener('click', handleClickOutsideUserList, false);

        toggleDisabledStateOnApplyBtn();
    };
    const handleResetUser = () => {
        searchCreatorInput.value = '';

        creatorInput.value = '';
        creatorInput.removeAttribute('disabled');

        toggleDisabledStateOnApplyBtn();
    };
    const handleClickOutsideUserList = (event) => {
        if (event.target.closest('.ibexa-filters__item--creator')) {
            return;
        }

        creatorInput.value = '';
        usersList.classList.add('ibexa-filters__user-list--hidden');
        doc.querySelector('body').removeEventListener('click', handleClickOutsideUserList, false);
    };
    const removeSearchTag = (event) => {
        const tag = event.currentTarget.closest(SELECTOR_TAG);
        const form = event.currentTarget.closest('form');

        ibexa.helpers.tooltips.hideAll();
        tag.remove();
        form.submit();
    };
    const clearContentType = (event) => {
        const checkbox = doc.querySelector(event.currentTarget.dataset.targetSelector);

        checkbox.checked = false;
        removeSearchTag(event);
    };
    const clearSection = (event) => {
        sectionSelect[0].selected = true;
        removeSearchTag(event);
    };
    const clearSubtree = (event) => {
        subtreeInput.value = '';
        removeSearchTag(event);
    };
    const clearDataRange = (event, select, dateRange) => {
        select.clearCurrentSelection();
        dateRange.clearDates();
        dateRange.toggleHidden(true);
        removeSearchTag(event);
    };
    const clearCreator = (event) => {
        handleResetUser();
        removeSearchTag(event);
    };
    const clearSearchTagBtnMethods = {
        section: (event) => clearSection(event),
        subtree: (event) => clearSubtree(event),
        creator: (event) => clearCreator(event),
        'content-types': (event) => clearContentType(event),
        'last-modified': (event) => clearDataRange(event, lastModifiedSelect, lastModifiedDateRange),
        'last-created': (event) => clearDataRange(event, lastCreatedSelect, lastCreatedDateRange),
    };
    const showMoreContentTypes = (event) => {
        const btn = event.currentTarget;
        const contentTypesList = btn
            .closest('.ibexa-content-type-selector__list-wrapper')
            .querySelector('.ibexa-content-type-selector__list[hidden]');

        btn.setAttribute('hidden', true);
        contentTypesList.removeAttribute('hidden');
    };
    const selectSubtreeWidget = new ibexa.core.TagViewSelect({
        fieldContainer: doc.querySelector('.ibexa-filters__item--subtree'),
    });
    const udwContainer = doc.getElementById('react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const confirmSubtreeUDW = (data) => {
        ibexa.helpers.tagViewSelect.buildItemsFromUDWResponse(
            data,
            (item) => item.pathString,
            (items) => {
                selectSubtreeWidget.addItems(items, true);

                closeUDW();
            },
        );
    };
    const openSubtreeUDW = (event) => {
        event.preventDefault();

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);

        udwRoot = ReactDOM.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: confirmSubtreeUDW.bind(this),
                onCancel: closeUDW,
                multiple: true,
                ...config,
            }),
        );
    };

    filterByContentType();

    clearBtn.addEventListener('click', clearFilters, false);

    if (sectionSelect) {
        sectionSelect.addEventListener('change', toggleDisabledStateOnApplyBtn, false);
    }

    for (const tagType in clearSearchTagBtnMethods) {
        const tagBtns = doc.querySelectorAll(`.ibexa-tag__remove-btn--${tagType}`);

        tagBtns.forEach((btn) => btn.addEventListener('click', clearSearchTagBtnMethods[tagType], false));
    }

    subtreeInput.addEventListener('change', toggleDisabledStateOnApplyBtn, false);
    lastModifiedSelectNode.addEventListener(
        'change',
        () => toggleDatesSelectVisibility(lastModifiedSelectNode, lastModifiedDateRange),
        false,
    );
    lastCreatedSelectNode.addEventListener('change', () => toggleDatesSelectVisibility(lastCreatedSelectNode, lastCreatedDateRange), false);
    creatorInput.addEventListener('keyup', handleTyping, false);
    usersList.addEventListener('click', handleSelectUser, false);
    contentTypeCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', filterByContentType, false));
    showMoreBtns.forEach((showMoreBtn) => showMoreBtn.addEventListener('click', showMoreContentTypes, false));
    selectSubtreeBtn.addEventListener('click', openSubtreeUDW, false);
})(window, window.document, window.ibexa, window.flatpickr, window.React, window.ReactDOM);
