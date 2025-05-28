import React, { useContext, useState, useEffect, useRef, useMemo } from 'react';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Icon from '../../../common/icon/icon';
import Dropdown from '../../../common/dropdown/dropdown';

import {
    DropdownPortalRefContext,
    CreateContentWidgetContext,
    ActiveTabContext,
    ContentOnTheFlyDataContext,
    MarkedLocationIdContext,
    LoadedLocationsMapContext,
    ContentOnTheFlyConfigContext,
    AllowedContentTypesContext,
    SuggestionsStorageContext,
} from '../../universal.discovery.module';

import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import {
    getAdminUiConfig,
    getTranslator,
    SYSTEM_ROOT_LOCATION_ID,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const MINIMUM_ITEMS_COUNT_FOR_SEARCH_TO_APPEAR = 10;

const ContentCreateWidget = () => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const refContentTree = useRef(null);
    const dropdownListRef = useContext(DropdownPortalRefContext);
    const [markedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap] = useContext(LoadedLocationsMapContext);
    const { allowedLanguages, preselectedLanguage, preselectedContentType } = useContext(ContentOnTheFlyConfigContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const [suggestionsStorage] = useContext(SuggestionsStorageContext);
    const { languages, contentTypes } = adminUiConfig;
    const selectedLocation = loadedLocationsMap.find((loadedLocation) => loadedLocation.parentLocationId === markedLocationId);
    const mappedLanguages = languages.priority.map((languageCode) => {
        return languages.mappings[languageCode];
    });
    const filteredLanguages = mappedLanguages.filter((language) => {
        const userHasPermission =
            !selectedLocation ||
            !selectedLocation.permissions ||
            !selectedLocation.permissions.create.restrictedLanguageCodes.length ||
            selectedLocation.permissions.create.restrictedLanguageCodes.includes(language.languageCode);
        const isAllowedLanguage = !allowedLanguages || allowedLanguages.includes(language.languageCode);

        return userHasPermission && isAllowedLanguage && language.enabled;
    });
    const [filterQuery, setFilterQuery] = useState('');
    const firstLanguageCode = filteredLanguages.length ? filteredLanguages[0].languageCode : '';
    const [selectedLanguage, setSelectedLanguage] = useState(preselectedLanguage || firstLanguageCode);
    const [selectedContentType, setSelectedContentType] = useState(preselectedContentType);
    const [isSelectedSuggestion, setIsSelectedSuggestion] = useState(false);
    const [, setActiveTab] = useContext(ActiveTabContext);
    const [createContentVisible, setCreateContentVisible] = useContext(CreateContentWidgetContext);
    const [, setContentOnTheFlyData] = useContext(ContentOnTheFlyDataContext);
    const close = () => {
        setCreateContentVisible(false);
    };
    const updateFilterQuery = (event) => {
        const query = event.target.value.toLowerCase();

        setFilterQuery(query);
    };
    const updateSelectedLanguage = (value) => setSelectedLanguage(value);
    const isConfirmDisabled = !selectedContentType || !selectedLanguage || markedLocationId === SYSTEM_ROOT_LOCATION_ID;
    const createContent = () => {
        setContentOnTheFlyData({
            locationId: markedLocationId,
            languageCode: selectedLanguage,
            contentTypeIdentifier: selectedContentType,
        });
        setActiveTab('content-create');
    };
    const createContentLabel = Translator.trans(
        /* @Desc("Create content") */ 'create_content.label',
        {},
        'ibexa_universal_discovery_widget',
    );
    const selectLanguageLabel = Translator.trans(
        /* @Desc("Select a language") */ 'create_content.select_language',
        {},
        'ibexa_universal_discovery_widget',
    );
    const createLabel = Translator.trans(/* @Desc("Create") */ 'create_content.create', {}, 'ibexa_universal_discovery_widget');
    const cancelLabel = Translator.trans(/* @Desc("Discard") */ 'content_create.cancel.label', {}, 'ibexa_universal_discovery_widget');
    const placeholder = Translator.trans(
        /* @Desc("Search by content type") */ 'content_create.placeholder',
        {},
        'ibexa_universal_discovery_widget',
    );
    const filtersDescLabel = Translator.trans(
        /* @Desc("Select a content type from list") */ 'content.create.filters.desc',
        {},
        'ibexa_universal_discovery_widget',
    );
    const createUnderLabel = Translator.trans(
        /* @Desc("Location: %location%") */ 'content.create.editing_details',
        { location: selectedLocation?.location?.ContentInfo.Content.TranslatedName },
        'ibexa_universal_discovery_widget',
    );
    const widgetClassName = createCssClassNames({
        'ibexa-extra-actions': true,
        'ibexa-extra-actions--create': true,
        'ibexa-extra-actions--hidden': !createContentVisible,
        'c-content-create': true,
    });
    const languageOptions = mappedLanguages
        .filter((language) => language.enabled)
        .map((language) => ({
            value: language.languageCode,
            label: language.name,
        }));
    const contentTypesWithSuggestions = Object.entries(contentTypes);
    const suggestions = suggestionsStorage[selectedLocation?.parentLocationId] ?? [];

    if (suggestions) {
        contentTypesWithSuggestions.unshift(['Suggestions', suggestions.map(({ data }) => data)]);
    }

    const { contentTypesToShow, allGroupsItemsCount } = useMemo(
        () =>
            contentTypesWithSuggestions.reduce(
                (
                    { contentTypesToShow: contentTypesToShowPrevious, allGroupsItemsCount: allGroupsItemsCountPrevious },
                    [groupName, groupItems],
                ) => {
                    const restrictedContentTypeIds = selectedLocation?.permissions?.create.restrictedContentTypeIds ?? [];
                    const groupFilteredItems = [...groupItems].filter((groupItem) => {
                        const hasNotPermission =
                            restrictedContentTypeIds.length && !restrictedContentTypeIds.includes(groupItem.id.toString());
                        const isNotAllowedContentType = allowedContentTypes && !allowedContentTypes.includes(groupItem.identifier);
                        const isHiddenByConfig = groupItem.isHidden;

                        return !hasNotPermission && !isNotAllowedContentType && !isHiddenByConfig;
                    });

                    const hasAnyItems = !!groupFilteredItems.length;

                    if (!hasAnyItems) {
                        return { contentTypesToShow: contentTypesToShowPrevious, allGroupsItemsCount: allGroupsItemsCountPrevious };
                    }

                    return {
                        contentTypesToShow: [...contentTypesToShowPrevious, [groupName, groupFilteredItems]],
                        allGroupsItemsCount: allGroupsItemsCountPrevious + groupFilteredItems.length,
                    };
                },
                { contentTypesToShow: [], allGroupsItemsCount: 0 },
            ),
        [contentTypesWithSuggestions, selectedLocation, allowedContentTypes],
    );
    const instantFilterInputWrapperClassName = createCssClassNames({
        'ibexa-instant-filter__input-wrapper': true,
        'ibexa-instant-filter__input-wrapper--hidden': allGroupsItemsCount <= MINIMUM_ITEMS_COUNT_FOR_SEARCH_TO_APPEAR,
    });

    useEffect(() => {
        setSelectedLanguage(preselectedLanguage || firstLanguageCode);
    }, [preselectedLanguage, firstLanguageCode]);

    useEffect(() => {
        parseTooltip(refContentTree.current);
    }, []);

    return (
        <div className="ibexa-extra-actions-container">
            <div className="ibexa-extra-actions-container__backdrop" hidden={!createContentVisible} onClick={close} />
            <div className={widgetClassName} ref={refContentTree}>
                <div className="ibexa-extra-actions__header">
                    <h3>{createContentLabel}</h3>
                    <div className="ibexa-extra-actions__header-subtitle">{createUnderLabel}</div>
                </div>
                <div className="ibexa-extra-actions__content ibexa-extra-actions__content--create">
                    <label className="ibexa-label ibexa-extra-actions__section-header">{selectLanguageLabel}</label>
                    <div className="ibexa-extra-actions__section-content">
                        <Dropdown
                            dropdownListRef={dropdownListRef}
                            onChange={updateSelectedLanguage}
                            single={true}
                            value={selectedLanguage}
                            options={languageOptions}
                            extraClasses="c-udw-dropdown"
                        />
                    </div>
                    <div className="ibexa-extra-actions__section-content ibexa-extra-actions__section-content--content-type">
                        <div className="ibexa-instant-filter">
                            <div className={instantFilterInputWrapperClassName}>
                                <input
                                    autoFocus={true}
                                    className="ibexa-instant-filter__input ibexa-input ibexa-input--text form-control"
                                    type="text"
                                    placeholder={placeholder}
                                    onChange={updateFilterQuery}
                                />
                            </div>
                        </div>
                        <div className="ibexa-instant-filter__desc">{filtersDescLabel}</div>
                        <div className="ibexa-instant-filter__items">
                            {contentTypesToShow.map(([groupName, groupItems], index) => {
                                const isSuggestionGroup = !!suggestions.length && index === 0;
                                const visibleGroupItems = groupItems.filter((groupItem) => {
                                    const isSearchedName = !filterQuery || groupItem.name.toLowerCase().includes(filterQuery);

                                    return isSearchedName;
                                });

                                if (visibleGroupItems.length === 0) {
                                    return null;
                                }

                                return (
                                    <div className="ibexa-instant-filter__group" key={groupName}>
                                        <div className="ibexa-instant-filter__group-name">{groupName}</div>
                                        {visibleGroupItems.map(({ name, thumbnail, identifier }) => {
                                            const className = createCssClassNames({
                                                'ibexa-instant-filter__group-item': true,
                                                'ibexa-instant-filter__group-item--selected':
                                                    identifier === selectedContentType && isSuggestionGroup === isSelectedSuggestion,
                                            });
                                            const updateSelectedContentType = () => {
                                                setSelectedContentType(identifier);
                                                setIsSelectedSuggestion(isSuggestionGroup);
                                            };

                                            return (
                                                <div key={identifier} className={className} onClick={updateSelectedContentType}>
                                                    <Icon customPath={thumbnail} extraClasses="ibexa-icon--small" />
                                                    <div className="form-check">
                                                        <div className="ibexa-label ibexa-label--checkbox-radio form-check-label">
                                                            {name}
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
                <div className="c-content-create__confirm-wrapper">
                    <button
                        className="c-content-create__confirm-button btn ibexa-btn ibexa-btn--primary"
                        onClick={createContent}
                        disabled={isConfirmDisabled}
                        type="button"
                    >
                        {createLabel}
                    </button>
                    <button className="btn ibexa-btn ibexa-btn--secondary" onClick={close} type="button">
                        {cancelLabel}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default ContentCreateWidget;
