import React, { useContext, useState, useEffect, useCallback, useRef } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import {
    SelectedContentTypesContext,
    SelectedSectionContext,
    SelectedSubtreeContext,
    SelectedLanguageContext,
    SelectedSubtreeBreadcrumbsContext,
} from '../search/search';

import UniversalDiscoveryModule, { DropdownPortalRefContext } from '../../universal.discovery.module';

import Dropdown from '../../../common/dropdown/dropdown';
import ContentTypeSelector from '../content-type-selector/content.type.selector';
import Icon from '../../../common/icon/icon';

import {
    removeRootFromPathString,
    findLocationsByIds,
    buildLocationsBreadcrumbs,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/location.helper';
import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const Filters = ({ search }) => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const [selectedContentTypes, dispatchSelectedContentTypesAction] = useContext(SelectedContentTypesContext);
    const [selectedSection, setSelectedSection] = useContext(SelectedSectionContext);
    const [selectedSubtree, setSelectedSubtree] = useContext(SelectedSubtreeContext);
    const [selectedLanguage, setSelectedLanguage] = useContext(SelectedLanguageContext);
    const [selectedSubtreeBreadcrumbs, setSelectedSubtreeBreadcrumbs] = useContext(SelectedSubtreeBreadcrumbsContext);
    const prevSelectedLanguage = useRef(selectedLanguage);
    const dropdownListRef = useContext(DropdownPortalRefContext);
    const [filtersCleared, setFiltersCleared] = useState(false);
    const [isNestedUdwOpened, setIsNestedUdwOpened] = useState(false);
    const filterSubtreeUdwConfig = JSON.parse(window.document.querySelector('#react-udw').dataset.filterSubtreeUdwConfig);
    const handleNestedUdwConfirm = (items) => {
        const [{ pathString }] = items;

        findLocationsByIds(removeRootFromPathString(pathString), (locations) =>
            setSelectedSubtreeBreadcrumbs(buildLocationsBreadcrumbs(locations), pathString),
        );

        setSelectedSubtree(pathString);
        setIsNestedUdwOpened(false);
    };
    const nestedUdwConfig = {
        onConfirm: handleNestedUdwConfirm,
        onCancel: () => setIsNestedUdwOpened(false),
        tabs: adminUiConfig.universalDiscoveryWidget.tabs,
        title: 'Browsing content',
        ...filterSubtreeUdwConfig,
    };
    const nestedUdwContainer = useRef(window.document.createElement('div'));
    const updateSelectedLanguage = (value) => setSelectedLanguage(value);
    const clearFilters = () => {
        dispatchSelectedContentTypesAction({ type: 'CLEAR_CONTENT_TYPES' });
        setSelectedSection('');
        clearSelectedSubtree();
        setFiltersCleared(true);
    };
    const clearSelectedSubtree = () => {
        setSelectedSubtree('');
        setSelectedSubtreeBreadcrumbs('');
    };
    const updateSection = (value) => setSelectedSection(value);
    const makeSearch = useCallback(() => search(0), [search]);
    const isApplyButtonEnabled =
        !!selectedContentTypes.length || !!selectedSection || !!selectedSubtree || prevSelectedLanguage.current !== selectedLanguage;
    const renderSubtreeBreadcrumbs = () => {
        if (!selectedSubtreeBreadcrumbs) {
            return null;
        }

        return (
            <div className="ibexa-tag-view-select__selected-list">
                <div className="ibexa-tag-view-select__selected-item-tag">
                    {selectedSubtreeBreadcrumbs}
                    <button
                        type="button"
                        className="btn ibexa-tag-view-select__selected-item-tag-remove-btn"
                        onClick={clearSelectedSubtree}
                    >
                        <Icon name="discard" extraClasses="ibexa-icon--tiny" />
                    </button>
                </div>
            </div>
        );
    };
    const renderSelectContentButton = () => {
        const selectLabel = Translator.trans(
            /*@Desc("Select content")*/ 'filters.tag_view_select.select',
            {},
            'ibexa_universal_discovery_widget',
        );
        const changeLabel = Translator.trans(
            /*@Desc("Change content")*/ 'filters.tag_view_change.select',
            {},
            'ibexa_universal_discovery_widget',
        );

        return (
            <button
                className="ibexa-tag-view-select__btn-select-path btn ibexa-btn ibexa-btn--secondary"
                type="button"
                onClick={() => setIsNestedUdwOpened(true)}
            >
                {selectedSubtree ? changeLabel : selectLabel}
            </button>
        );
    };
    const filtersLabel = Translator.trans(/*@Desc("Filters")*/ 'filters.title', {}, 'ibexa_universal_discovery_widget');
    const languageLabel = Translator.trans(/*@Desc("Language")*/ 'filters.language', {}, 'ibexa_universal_discovery_widget');
    const sectionLabel = Translator.trans(/*@Desc("Section")*/ 'filters.section', {}, 'ibexa_universal_discovery_widget');
    const subtreeLabel = Translator.trans(/*@Desc("Subtree")*/ 'filters.subtree', {}, 'ibexa_universal_discovery_widget');
    const clearLabel = Translator.trans(/*@Desc("Clear")*/ 'filters.clear', {}, 'ibexa_universal_discovery_widget');
    const applyLabel = Translator.trans(/*@Desc("Apply")*/ 'filters.apply', {}, 'ibexa_universal_discovery_widget');
    const languageOptions = Object.values(adminUiConfig.languages.mappings)
        .filter((language) => language.enabled)
        .map((language) => ({
            value: language.languageCode,
            label: language.name,
        }));
    const sectionOptions = Object.entries(adminUiConfig.sections).map(([sectionIdentifier, sectionName]) => ({
        value: sectionIdentifier,
        label: sectionName,
    }));

    useEffect(() => {
        if (filtersCleared) {
            setFiltersCleared(false);
            makeSearch();
        }
    }, [filtersCleared, makeSearch]);

    useEffect(() => {
        window.document.body.append(nestedUdwContainer.current);

        return () => {
            nestedUdwContainer.current.remove();
        };
    });

    return (
        <>
            {isNestedUdwOpened && ReactDOM.createPortal(<UniversalDiscoveryModule {...nestedUdwConfig} />, nestedUdwContainer.current)}
            <div className="c-filters">
                <div className="c-filters__header">
                    <div className="c-filters__header-content">{filtersLabel}</div>
                    <div className="c-filters__header-actions">
                        <button className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--small" type="button" onClick={clearFilters}>
                            {clearLabel}
                        </button>
                        <button
                            type="submit"
                            className="btn ibexa-btn ibexa-btn--secondary ibexa-btn--small ibexa-btn--apply"
                            onClick={makeSearch}
                            disabled={!isApplyButtonEnabled}
                        >
                            {applyLabel}
                        </button>
                    </div>
                </div>
                <div className="c-filters__row c-filters__row--language">
                    <div className="c-filters__row-title">{languageLabel}</div>
                    <Dropdown
                        dropdownListRef={dropdownListRef}
                        single={true}
                        onChange={updateSelectedLanguage}
                        value={selectedLanguage}
                        options={languageOptions}
                        extraClasses="c-udw-dropdown"
                    />
                </div>
                <ContentTypeSelector />
                <div className="c-filters__row">
                    <div className="c-filters__row-title">{sectionLabel}</div>
                    <Dropdown
                        dropdownListRef={dropdownListRef}
                        single={true}
                        onChange={updateSection}
                        value={selectedSection}
                        options={sectionOptions}
                        extraClasses="c-udw-dropdown"
                    />
                </div>
                <div className="c-filters__row">
                    <div className="c-filters__row-title">{subtreeLabel}</div>
                    <div className="ibexa-tag-view-select">
                        {renderSubtreeBreadcrumbs()}
                        {renderSelectContentButton()}
                    </div>
                </div>
            </div>
        </>
    );
};

Filters.propTypes = {
    search: PropTypes.func.isRequired,
};

export default Filters;
