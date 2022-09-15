import React, { useContext, useState, useEffect, useRef } from 'react';

import Icon from '../../../common/icon/icon';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import {
    DropdownPortalRefContext,
    CreateContentWidgetContext,
    ActiveTabContext,
    ContentOnTheFlyDataContext,
    MarkedLocationIdContext,
    LoadedLocationsMapContext,
    ContentOnTheFlyConfigContext,
    AllowedContentTypesContext,
} from '../../universal.discovery.module';
import Dropdown from '../../../common/dropdown/dropdown';

const { Translator, ibexa } = window;

const configLanguages = ibexa.adminUiConfig.languages;
const languages = configLanguages.priority.map((languageCode) => {
    return configLanguages.mappings[languageCode];
});
const contentTypes = Object.entries(ibexa.adminUiConfig.contentTypes);

const ContentCreateWidget = () => {
    const refContentTree = useRef(null);
    const dropdownListRef = useContext(DropdownPortalRefContext);
    const [markedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap] = useContext(LoadedLocationsMapContext);
    const { allowedLanguages, preselectedLanguage, preselectedContentType } = useContext(ContentOnTheFlyConfigContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const selectedLocation = loadedLocationsMap.find((loadedLocation) => loadedLocation.parentLocationId === markedLocationId);
    const filteredLanguages = languages.filter((language) => {
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
    const isConfirmDisabled = !selectedContentType || !selectedLanguage || markedLocationId === 1;
    const createContent = () => {
        setContentOnTheFlyData({
            locationId: markedLocationId,
            languageCode: selectedLanguage,
            contentTypeIdentifier: selectedContentType,
        });
        setActiveTab('content-create');
    };
    const createContentLabel = Translator.trans(/*@Desc("Create new content")*/ 'create_content.label', {}, 'universal_discovery_widget');
    const selectLanguageLabel = Translator.trans(
        /*@Desc("Select a language")*/ 'create_content.select_language',
        {},
        'universal_discovery_widget',
    );
    const selectContentType = Translator.trans(
        /*@Desc("Select a Content Type")*/ 'create_content.select_content_type',
        {},
        'universal_discovery_widget',
    );
    const createLabel = Translator.trans(/*@Desc("Create new")*/ 'create_content.create', {}, 'universal_discovery_widget');
    const closeLabel = Translator.trans(/*@Desc("Close")*/ 'popup.close.label', {}, 'universal_discovery_widget');
    const cancelLabel = Translator.trans(/*@Desc("Cancel")*/ 'content_create.cancel.label', {}, 'universal_discovery_widget');
    const placeholder = Translator.trans(/*@Desc("Type to refine")*/ 'content_create.placeholder', {}, 'universal_discovery_widget');
    const filtersDescLabel = Translator.trans(
        /*@Desc("Or choose from list")*/ 'content.create.filters.desc',
        {},
        'universal_discovery_widget',
    );
    const createUnderLabel = Translator.trans(
        /*@Desc("under %content_name%")*/ 'content.create.editing_details',
        { content_name: selectedLocation?.location?.ContentInfo.Content.TranslatedName },
        'universal_discovery_widget',
    );
    const widgetClassName = createCssClassNames({
        'ibexa-extra-actions': true,
        'ibexa-extra-actions--create': true,
        'ibexa-extra-actions--hidden': !createContentVisible,
        'c-content-create': true,
    });
    const languageOptions = languages
        .filter((language) => language.enabled)
        .map((language) => ({
            value: language.languageCode,
            label: language.name,
        }));

    useEffect(() => {
        setSelectedLanguage(preselectedLanguage || firstLanguageCode);
    }, [preselectedLanguage, firstLanguageCode]);

    useEffect(() => {
        ibexa.helpers.tooltips.parse(refContentTree.current);
    }, []);

    return (
        <div className="ibexa-extra-actions-container">
            <div className="ibexa-extra-actions-container__backdrop" hidden={!createContentVisible} onClick={close} />
            <div className={widgetClassName} ref={refContentTree}>
                <div className="ibexa-extra-actions__header">
                    <h3>{createContentLabel}</h3>
                    <button
                        type="button"
                        className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-btn--close"
                        onClick={close}
                        title={closeLabel}
                        data-tooltip-container-selector=".c-udw-tab"
                    >
                        <Icon name="discard" extraClasses="ibexa-icon--small" />
                    </button>
                    <div className="ibexa-extra-actions__header-subtitle">{createUnderLabel}</div>
                </div>
                <div className="ibexa-extra-actions__content">
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
                    <label className="ibexa-label ibexa-extra-actions__section-header">{selectContentType}</label>
                    <div className="ibexa-extra-actions__section-content ibexa-extra-actions__section-content--content-type">
                        <div className="ibexa-instant-filter">
                            <div className="ibexa-instant-filter__input-wrapper">
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
                            {contentTypes.map(([groupName, groupItems]) => {
                                const restrictedContentTypeIds = selectedLocation?.permissions?.create.restrictedContentTypeIds ?? [];
                                const isHiddenGroup = groupItems.every((groupItem) => {
                                    const isNotSearchedName = filterQuery && !groupItem.name.toLowerCase().includes(filterQuery);
                                    const hasNotPermission =
                                        restrictedContentTypeIds.length && !restrictedContentTypeIds.includes(groupItem.id.toString());
                                    const isNotAllowedContentType =
                                        allowedContentTypes && !allowedContentTypes.includes(groupItem.identifier);
                                    const isHiddenByConfig = groupItem.isHidden;

                                    return isNotSearchedName || hasNotPermission || isNotAllowedContentType || isHiddenByConfig;
                                });

                                if (isHiddenGroup) {
                                    return null;
                                }

                                return (
                                    <div className="ibexa-instant-filter__group" key={groupName}>
                                        <div className="ibexa-instant-filter__group-name">{groupName}</div>
                                        {groupItems.map(({ name, thumbnail, identifier, id, isHidden: isHiddenByConfig }) => {
                                            const isHidden =
                                                isHiddenByConfig ||
                                                (filterQuery && !name.toLowerCase().includes(filterQuery)) ||
                                                (selectedLocation &&
                                                    selectedLocation.permissions &&
                                                    selectedLocation.permissions.create.restrictedContentTypeIds.length &&
                                                    !selectedLocation.permissions.create.restrictedContentTypeIds.includes(
                                                        id.toString(),
                                                    )) ||
                                                (allowedContentTypes && !allowedContentTypes.includes(identifier));
                                            const className = createCssClassNames({
                                                'ibexa-instant-filter__group-item': true,
                                                'ibexa-instant-filter__group-item--selected': identifier === selectedContentType,
                                            });
                                            const updateSelectedContentType = () => setSelectedContentType(identifier);

                                            if (isHidden) {
                                                return null;
                                            }

                                            return (
                                                <div
                                                    hidden={isHidden}
                                                    key={identifier}
                                                    className={className}
                                                    onClick={updateSelectedContentType}
                                                >
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
