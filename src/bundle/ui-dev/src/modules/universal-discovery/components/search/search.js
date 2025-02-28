import React, { useState, useEffect, useReducer, useContext, createContext, useRef } from 'react';
import PropTypes from 'prop-types';

export const SelectedLanguageContext = createContext();
export const SelectedContentTypesContext = createContext();
export const SelectedSectionContext = createContext();
export const SelectedSubtreeContext = createContext();
export const SelectedSubtreeBreadcrumbsContext = createContext();

import Spinner from '../../../common/spinner/spinner';
import ContentTable from '../content-table/content.table';
import Filters from '../filters/filters';
import ContentMetaPreview from '../../content.meta.preview.module';
import SearchTags from './search.tags';
import { useSearchByQueryFetch } from '../../hooks/useSearchByQueryFetch';
import { ActiveTabContext, AllowedContentTypesContext, MarkedLocationIdContext, SearchTextContext } from '../../universal.discovery.module';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import SearchNoResults from './search.no.results';

const selectedContentTypesReducer = (state, action) => {
    switch (action.type) {
        case 'ADD_CONTENT_TYPE':
            return [...state, action.contentTypeIdentifier];
        case 'REMOVE_CONTENT_TYPE':
            return state.filter((contentTypeIdentifier) => contentTypeIdentifier !== action.contentTypeIdentifier);
        case 'CLEAR_CONTENT_TYPES':
            return [];
        default:
            throw new Error();
    }
};

const Search = ({ itemsPerPage }) => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const [, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [, setActiveTab, previousActiveTab, initialActiveTab] = useContext(ActiveTabContext);
    const [searchText, setSearchText] = useContext(SearchTextContext);
    const [offset, setOffset] = useState(0);
    const [selectedContentTypes, dispatchSelectedContentTypesAction] = useReducer(selectedContentTypesReducer, []);
    const [selectedSection, setSelectedSection] = useState('');
    const [selectedSubtree, setSelectedSubtree] = useState('');
    const [selectedSubtreeBreadcrumbs, setSelectedSubtreeBreadcrumbs] = useState('');
    const { languages } = adminUiConfig;
    const mappedLanguages = languages.priority.map((value) => {
        return languages.mappings[value];
    });
    const firstLanguageCode = mappedLanguages.length ? mappedLanguages[0].languageCode : '';
    const [selectedLanguage, setSelectedLanguage] = useState(firstLanguageCode);
    const prevSearchText = useRef(null);
    const [isLoading, data, searchByQuery] = useSearchByQueryFetch();
    const search = () => {
        const shouldResetOffset = prevSearchText.current !== searchText && offset !== 0;

        prevSearchText.current = searchText;

        if (shouldResetOffset) {
            setOffset(0);

            return;
        }

        const contentTypes = !!selectedContentTypes.length ? [...selectedContentTypes] : allowedContentTypes;

        setMarkedLocationId(null);
        searchByQuery(searchText, contentTypes, selectedSection, selectedSubtree, itemsPerPage, offset, selectedLanguage);
    };
    const changePage = (pageIndex) => setOffset(pageIndex * itemsPerPage);
    const handleResultsClear = () => {
        const activeTabNew = previousActiveTab ?? initialActiveTab;

        setActiveTab(activeTabNew);
        setSearchText('');
    };
    const renderCustomTableHeader = () => {
        const selectedLanguageName = languages.mappings[selectedLanguage].name;
        const searchResultsTitle = Translator.trans(
            /*@Desc("Results for “%search_phrase%” (%total%)")*/ 'search.search_results',
            {
                search_phrase: searchText,
                total: data.count,
            },
            'ibexa_universal_discovery_widget',
        );
        const searchResultsSubtitle = Translator.trans(
            /*@Desc("in %search_language%")*/ 'search.search_results.in_language',
            { search_language: selectedLanguageName },
            'ibexa_universal_discovery_widget',
        );
        const searchResultsClearBtnLabel = Translator.trans(
            /*@Desc("Clear results")*/ 'search.search_results.clear_btn.label',
            {},
            'ibexa_universal_discovery_widget',
        );

        return (
            <>
                <div className="ibexa-table-header c-search__table-header">
                    <div className="ibexa-table-header__headline c-search__table-title">{searchResultsTitle}</div>
                    <button
                        type="button"
                        className="btn ibexa-btn ibexa-btn--secondary ibexa-btn--small c-search__clear-results-btn"
                        onClick={handleResultsClear}
                    >
                        {searchResultsClearBtnLabel}
                    </button>
                    <div className="c-search__table-subtitle">{searchResultsSubtitle}</div>
                    <div className="c-search__search-tags">
                        <SearchTags />
                    </div>
                </div>
            </>
        );
    };
    const renderSearchResults = () => {
        if (data.count) {
            return (
                <ContentTable
                    count={data.count}
                    items={data.items}
                    itemsPerPage={itemsPerPage}
                    activePageIndex={offset ? offset / itemsPerPage : 0}
                    onPageChange={changePage}
                    renderCustomHeader={renderCustomTableHeader}
                />
            );
        } else if (!!data.items) {
            return (
                <>
                    {renderCustomTableHeader()}
                    <SearchNoResults searchText={searchText} />
                </>
            );
        }
    };
    const spinnerWrapperClassName = createCssClassNames({
        'c-search__spinner-wrapper': true,
        'c-search__spinner-wrapper--show': isLoading,
    });

    useEffect(search, [searchText, offset]);

    return (
        <div className="c-search">
            <SelectedContentTypesContext.Provider value={[selectedContentTypes, dispatchSelectedContentTypesAction]}>
                <SelectedSectionContext.Provider value={[selectedSection, setSelectedSection]}>
                    <SelectedSubtreeContext.Provider value={[selectedSubtree, setSelectedSubtree]}>
                        <SelectedSubtreeBreadcrumbsContext.Provider value={[selectedSubtreeBreadcrumbs, setSelectedSubtreeBreadcrumbs]}>
                            <SelectedLanguageContext.Provider value={[selectedLanguage, setSelectedLanguage]}>
                                <div className="c-search__main">
                                    <div className="c-search__content">
                                        <div className={spinnerWrapperClassName}>
                                            <Spinner />
                                        </div>
                                        {renderSearchResults()}
                                    </div>
                                    <div className="c-search__content-meta-preview">
                                        <ContentMetaPreview />
                                    </div>
                                    <div className="c-search__filters">
                                        <Filters isCollapsed={false} search={search} />
                                    </div>
                                </div>
                            </SelectedLanguageContext.Provider>
                        </SelectedSubtreeBreadcrumbsContext.Provider>
                    </SelectedSubtreeContext.Provider>
                </SelectedSectionContext.Provider>
            </SelectedContentTypesContext.Provider>
        </div>
    );
};

Search.propTypes = {
    itemsPerPage: PropTypes.number,
};

Search.defaultProps = {
    itemsPerPage: 50,
};

export default Search;
