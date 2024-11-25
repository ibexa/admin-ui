import React, { useEffect, useCallback, useState, createContext, useRef, useMemo } from 'react';
import PropTypes from 'prop-types';

import Icon from '../common/icon/icon';
import deepClone from '../common/helpers/deep.clone.helper';
import { createCssClassNames } from '../common/helpers/css.class.names';
import { useLoadedLocationsReducer } from './hooks/useLoadedLocationsReducer';
import { useSelectedLocationsReducer } from './hooks/useSelectedLocationsReducer';
import {
    loadAccordionData,
    loadContentTypes,
    findLocationsById,
    loadContentInfo,
    loadLocationsWithPermissions,
} from './services/universal.discovery.service';

import {
    parse as parseTooltips,
    hideAll as hideAllTooltips,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import {
    getAdminUiConfig,
    getTranslator,
    SYSTEM_ROOT_LOCATION_ID,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { useSelectedItemsReducer } from './hooks/useSelectedItemsReducer';

const { document } = window;
const CLASS_SCROLL_DISABLED = 'ibexa-scroll-disabled';
const SEARCH_TAB_ID = 'search';
const defaultRestInfo = {
    accsessToken: null,
    instanceUrl: window.location.origin,
    token: document.querySelector('meta[name="CSRF-Token"]')?.content,
    siteaccess: document.querySelector('meta[name="SiteAccess"]')?.content,
};

export const SORTING_OPTIONS = [
    {
        value: 'date:asc',
        getLabel: () => {
            const Translator = getTranslator();

            return (
                <div className="c-simple-dropdown__option-label">
                    {Translator.trans(/*@Desc("Date")*/ 'sorting.date.label', {}, 'ibexa_universal_discovery_widget')}
                    <Icon name="back" extraClasses="c-simple-dropdown__arrow-down ibexa-icon--tiny-small" />
                </div>
            );
        },
        selectedLabel: () => {
            const Translator = getTranslator();

            return (
                <div className="c-simple-dropdown__option-label">
                    {Translator.trans(/*@Desc("Sort by date")*/ 'sorting.date.selected_label', {}, 'ibexa_universal_discovery_widget')}
                    <Icon name="back" extraClasses="c-simple-dropdown__arrow-down ibexa-icon--tiny-small" />
                </div>
            );
        },
        sortClause: 'DatePublished',
        sortOrder: 'ascending',
    },
    {
        value: 'date:desc',
        getLabel: () => {
            const Translator = getTranslator();

            return (
                <div className="c-simple-dropdown__option-label">
                    {Translator.trans(/*@Desc("Date")*/ 'sorting.date.label', {}, 'ibexa_universal_discovery_widget')}
                    <Icon name="back" extraClasses="c-simple-dropdown__arrow-up ibexa-icon--tiny-small" />
                </div>
            );
        },
        selectedLabel: () => {
            const Translator = getTranslator();

            return (
                <div className="c-simple-dropdown__option-label">
                    {Translator.trans(/*@Desc("Sort by date")*/ 'sorting.date.selected_label', {}, 'ibexa_universal_discovery_widget')}
                    <Icon name="back" extraClasses="c-simple-dropdown__arrow-up ibexa-icon--tiny-small" />
                </div>
            );
        },
        sortClause: 'DatePublished',
        sortOrder: 'descending',
    },
    {
        value: 'name:asc',
        getLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(/*@Desc("Name A-Z")*/ 'sorting.name.asc.label', {}, 'ibexa_universal_discovery_widget');
        },
        selectedLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(
                /*@Desc("Sort by name A-Z")*/ 'sorting.name.asc.selected_label',
                {},
                'ibexa_universal_discovery_widget',
            );
        },
        sortClause: 'ContentName',
        sortOrder: 'ascending',
    },
    {
        value: 'name:desc',
        getLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(/*@Desc("Name Z-A")*/ 'sorting.name.desc.label', {}, 'ibexa_universal_discovery_widget');
        },
        selectedLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(
                /*@Desc("Sort by name Z-A")*/ 'sorting.name.desc.selected_label',
                {},
                'ibexa_universal_discovery_widget',
            );
        },
        sortClause: 'ContentName',
        sortOrder: 'descending',
    },
];

export const VIEWS = [
    {
        value: 'finder',
        iconName: 'panels',
        getLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(/*@Desc("Panels view")*/ 'sorting.panels.view', {}, 'ibexa_universal_discovery_widget');
        },
    },
    {
        value: 'grid',
        iconName: 'view-grid',
        getLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(/*@Desc("Grid view")*/ 'sorting.grid.view', {}, 'ibexa_universal_discovery_widget');
        },
    },
    {
        value: 'tree',
        iconName: 'content-tree',
        getLabel: () => {
            const Translator = getTranslator();

            return Translator.trans(/*@Desc("Tree view")*/ 'sorting.tree.view', {}, 'ibexa_universal_discovery_widget');
        },
    },
];

export const SNACKBAR_ACTIONS = {
    INSERT: 'insert',
    DUPLICATE: 'duplicate',
    EDIT: 'edit',
    DOWNLOAD: 'download',
    DELETE: 'delete',
    TOGGLE_SELECTION: 'toggleSelection',
};

export const UDWContext = createContext();
export const RestInfoContext = createContext();
export const AllowRedirectsContext = createContext();
export const AllowConfirmationContext = createContext();
export const ContentTypesMapContext = createContext();
export const ContentTypesInfoMapContext = createContext();
export const MultipleConfigContext = createContext();
export const ContainersOnlyContext = createContext();
export const AllowedContentTypesContext = createContext();
export const ActiveTabContext = createContext();
export const TabsConfigContext = createContext();
export const TabsContext = createContext();
export const TitleContext = createContext();
export const CancelContext = createContext();
export const ConfirmContext = createContext();
export const SortingContext = createContext();
export const SortOrderContext = createContext();
export const CurrentViewContext = createContext();
export const MarkedLocationIdContext = createContext();
export const StartingLocationIdContext = createContext();
export const LoadedLocationsMapContext = createContext();
export const RootLocationIdContext = createContext();
export const SelectedLocationsContext = createContext();
export const SelectedItemsContext = createContext();
export const CreateContentWidgetContext = createContext();
export const ContentOnTheFlyDataContext = createContext();
export const ContentOnTheFlyConfigContext = createContext();
export const EditOnTheFlyDataContext = createContext();
export const BlockFetchLocationHookContext = createContext();
export const SearchTextContext = createContext();
export const DropdownPortalRefContext = createContext();
export const SuggestionsStorageContext = createContext();
export const GridActiveLocationIdContext = createContext();
export const SnackbarActionsContext = createContext();
export const ViewContext = createContext();

const UniversalDiscoveryModule = (props) => {
    const { restInfo } = props;
    const adminUiConfig = getAdminUiConfig();
    const { tabs } = adminUiConfig.universalDiscoveryWidget;
    const defaultMarkedLocationId = props.startingLocationId || props.rootLocationId;
    const abortControllerRef = useRef();
    const dropdownPortalRef = useRef();
    const [{ activeTab, previousActiveTab }, setActiveTabsData] = useState({
        activeTab: props.activeTab,
        previousActiveTab: null,
    });
    const setActiveTab = (activeTabNew) =>
        setActiveTabsData(({ activeTab: activeTabOld }) => ({
            activeTab: activeTabNew,
            previousActiveTab: activeTabOld,
        }));
    const [sorting, setSorting] = useState(props.activeSortClause);
    const [sortOrder, setSortOrder] = useState(props.activeSortOrder);
    const [currentView, setCurrentView] = useState(props.activeView);
    const [markedLocationId, setMarkedLocationId] = useState(defaultMarkedLocationId !== 1 ? defaultMarkedLocationId : null);
    const [createContentVisible, setCreateContentVisible] = useState(false);
    const [contentOnTheFlyData, setContentOnTheFlyData] = useState({});
    const [editOnTheFlyData, setEditOnTheFlyData] = useState({});
    const [contentTypesInfoMap, setContentTypesInfoMap] = useState({});
    const [isFetchLocationHookBlocked, setIsFetchLocationHookBlocked] = useState(
        props.startingLocationId && props.startingLocationId !== 1 && props.startingLocationId !== props.rootLocationId,
    );
    const [searchText, setSearchText] = useState('');
    const [suggestionsStorage, setSuggestionsStorage] = useState({});
    const [gridActiveLocationId, setGridActiveLocationId] = useState(markedLocationId);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useLoadedLocationsReducer([
        { parentLocationId: props.rootLocationId, subitems: [] },
    ]);
    const [selectedLocations, dispatchSelectedLocationsAction] = useSelectedLocationsReducer();
    const { selectedItems, dispatchSelectedItemsAction } = useSelectedItemsReducer({
        isMultiple: props.multiple,
        multipleItemsLimit: props.multipleItemsLimit,
    });
    const activeTabConfig = tabs.find((tab) => tab.id === activeTab);
    const Tab = activeTabConfig.component;
    const className = createCssClassNames({
        'm-ud': true,
        'm-ud--locations-selected': !!selectedLocations.length && props.allowConfirmation,
    });
    const loadPermissions = () => {
        const locationIds = selectedLocations
            .filter((item) => !item.permissions)
            .map((item) => item.location.id)
            .join(',');

        if (!locationIds) {
            return Promise.resolve([]);
        }

        return new Promise((resolve) => {
            loadLocationsWithPermissions({ ...restInfo, locationIds, signal: abortControllerRef.current.signal }, (response) =>
                resolve(response),
            );
        });
    };
    const loadVersions = (signal = null) => {
        const locationsWithoutVersion = selectedLocations.filter(
            (selectedItem) => !selectedItem.location.ContentInfo.Content.CurrentVersion.Version,
        );

        if (!locationsWithoutVersion.length) {
            return Promise.resolve([]);
        }

        const contentId = locationsWithoutVersion.map((item) => item.location.ContentInfo.Content._id).join(',');

        return new Promise((resolve) => {
            loadContentInfo({ ...restInfo, contentId, signal }, (response) => resolve(response));
        });
    };
    const contentTypesMapGlobal = useMemo(
        () =>
            Object.values(adminUiConfig.contentTypes).reduce((contentTypesMap, contentTypesGroup) => {
                contentTypesGroup.forEach((contentType) => {
                    contentTypesMap[contentType.href] = contentType;
                });

                return contentTypesMap;
            }, {}),
        [adminUiConfig.contentTypes],
    );
    const onConfirm = useCallback(
        (selection = selectedLocations) => {
            loadVersions().then((locationsWithVersions) => {
                const clonedSelectedLocation = deepClone(selection);

                if (Array.isArray(locationsWithVersions)) {
                    locationsWithVersions.forEach((content) => {
                        const clonedLocation = clonedSelectedLocation.find(
                            (clonedItem) => clonedItem.location.ContentInfo.Content._id === content._id,
                        );

                        if (clonedLocation) {
                            clonedLocation.location.ContentInfo.Content.CurrentVersion.Version = content.CurrentVersion.Version;
                        }
                    });
                }

                const updatedLocations = clonedSelectedLocation.map((selectedItem) => {
                    const clonedLocation = deepClone(selectedItem.location);
                    const contentType = clonedLocation.ContentInfo.Content.ContentType;

                    clonedLocation.ContentInfo.Content.ContentTypeInfo = contentTypesInfoMap[contentType._href];

                    return clonedLocation;
                });

                props.onConfirm(updatedLocations);
            });
        },
        [selectedLocations, contentTypesInfoMap],
    );
    const makeSearch = (value) => {
        if (activeTab !== SEARCH_TAB_ID) {
            setActiveTab('search');
        }

        setSearchText(value);
    };

    useEffect(() => {
        const addContentTypesInfo = (contentTypes) => {
            setContentTypesInfoMap((prevState) => ({ ...prevState, ...contentTypes }));
        };
        const handleLoadContentTypes = (response) => {
            const contentTypesMap = response.ContentTypeInfoList.ContentType.reduce((contentTypesList, item) => {
                contentTypesList[item._href] = item;

                return contentTypesList;
            }, {});

            addContentTypesInfo(contentTypesMap);
        };

        adminUiConfig.universalDiscoveryWidget.contentTypesLoaders?.forEach((contentTypesLoader) =>
            contentTypesLoader(addContentTypesInfo),
        );

        loadContentTypes(restInfo, handleLoadContentTypes);
        document.body.dispatchEvent(new CustomEvent('ibexa-udw-opened'));
        parseTooltips(document.querySelector('.c-udw-tab'));

        return () => {
            document.body.dispatchEvent(new CustomEvent('ibexa-udw-closed'));
            hideAllTooltips();
        };
    }, []);

    useEffect(() => {
        if (!props.selectedLocations.length) {
            return;
        }

        findLocationsById({ ...restInfo, id: props.selectedLocations.join(','), limit: props.selectedLocations.length }, (locations) => {
            const mappedLocation = props.selectedLocations.map((locationId) => {
                const location = locations.find(({ id }) => id === parseInt(locationId, 10));

                return { location };
            });

            dispatchSelectedLocationsAction({ type: 'REPLACE_SELECTED_LOCATIONS', locations: mappedLocation });
        });
    }, [props.selectedLocations]);

    useEffect(() => {
        abortControllerRef.current?.abort();
        abortControllerRef.current = new AbortController();

        Promise.all([loadPermissions(), loadVersions(abortControllerRef.current.signal)]).then((response) => {
            const [locationsWithPermissions, locationsWithVersions] = response;

            if (!locationsWithPermissions.length && !locationsWithVersions.length) {
                return;
            }

            const clonedSelectedLocation = deepClone(selectedLocations);

            locationsWithPermissions.LocationList.locations.forEach((item) => {
                const locationWithoutPermissions = clonedSelectedLocation.find(
                    (selectedItem) => selectedItem.location.id === item.location.Location.id,
                );

                if (locationWithoutPermissions) {
                    locationWithoutPermissions.permissions = item.permissions;
                }
            });

            locationsWithVersions.forEach((content) => {
                const clonedLocation = clonedSelectedLocation.find(
                    (clonedItem) => clonedItem.location.ContentInfo.Content._id === content._id,
                );

                if (clonedLocation) {
                    clonedLocation.location.ContentInfo.Content.CurrentVersion.Version = content.CurrentVersion.Version;
                }
            });

            dispatchSelectedLocationsAction({
                type: 'REPLACE_SELECTED_LOCATIONS',
                locations: clonedSelectedLocation,
            });
        });

        return () => {
            abortControllerRef.current?.abort();
        };
    }, [selectedLocations]);

    useEffect(() => {
        document.body.classList.add(CLASS_SCROLL_DISABLED);

        return () => {
            document.body.classList.remove(CLASS_SCROLL_DISABLED);
        };
    });

    useEffect(() => {
        if (currentView === 'grid') {
            if (loadedLocationsMap[loadedLocationsMap.length - 1]) {
                loadedLocationsMap[loadedLocationsMap.length - 1].subitems = [];
            }

            dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: loadedLocationsMap });
        } else if (
            (currentView === 'finder' || currentView === 'tree') &&
            !!markedLocationId &&
            markedLocationId !== loadedLocationsMap[loadedLocationsMap.length - 1].parentLocationId &&
            loadedLocationsMap[loadedLocationsMap.length - 1].subitems.find((subitem) => subitem.location.id === markedLocationId)
        ) {
            dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data: { parentLocationId: markedLocationId, subitems: [] } });
        }
    }, [currentView]);

    useEffect(() => {
        if (
            !props.startingLocationId ||
            props.startingLocationId === SYSTEM_ROOT_LOCATION_ID ||
            props.startingLocationId === props.rootLocationId
        ) {
            return;
        }

        loadAccordionData(
            {
                ...restInfo,
                parentLocationId: props.startingLocationId,
                sortClause: sorting,
                sortOrder: sortOrder,
                gridView: currentView === 'grid',
                rootLocationId: props.rootLocationId,
            },
            (locationsMap) => {
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: locationsMap });
                setMarkedLocationId(props.startingLocationId);
                setIsFetchLocationHookBlocked(false);
            },
        );
    }, [props.startingLocationId]);

    useEffect(() => {
        const locationsMap = loadedLocationsMap.map((loadedLocation) => {
            loadedLocation.subitems = [];

            return loadedLocation;
        });

        dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: locationsMap });
    }, [sorting, sortOrder]);

    useEffect(() => {
        if (currentView === 'grid') {
            setGridActiveLocationId(markedLocationId ?? defaultMarkedLocationId);
        }
    }, [currentView]);

    return (
        <div className={className}>
            <UDWContext.Provider value={true}>
                <RestInfoContext.Provider value={restInfo}>
                    <BlockFetchLocationHookContext.Provider value={[isFetchLocationHookBlocked, setIsFetchLocationHookBlocked]}>
                        <AllowRedirectsContext.Provider value={props.allowRedirects}>
                            <AllowConfirmationContext.Provider value={props.allowConfirmation}>
                                <ContentTypesInfoMapContext.Provider value={contentTypesInfoMap}>
                                    <ContentTypesMapContext.Provider value={contentTypesMapGlobal}>
                                        <MultipleConfigContext.Provider value={[true, 10]}>
                                            <ContainersOnlyContext.Provider value={props.containersOnly}>
                                                <AllowedContentTypesContext.Provider value={props.allowedContentTypes}>
                                                    <SnackbarActionsContext.Provider value={props.snackbarEnabledActions}>
                                                        <ActiveTabContext.Provider
                                                            value={[activeTab, setActiveTab, previousActiveTab, props.activeTab]}
                                                        >
                                                            <TabsContext.Provider value={tabs}>
                                                                <TabsConfigContext.Provider value={props.tabsConfig}>
                                                                    <TitleContext.Provider value={props.title}>
                                                                        <CancelContext.Provider value={props.onCancel}>
                                                                            <ConfirmContext.Provider value={onConfirm}>
                                                                                <SortingContext.Provider value={[sorting, setSorting]}>
                                                                                    <SortOrderContext.Provider
                                                                                        value={[sortOrder, setSortOrder]}
                                                                                    >
                                                                                        <CurrentViewContext.Provider
                                                                                            value={[currentView, setCurrentView]}
                                                                                        >
                                                                                            <ViewContext.Provider
                                                                                                value={{
                                                                                                    views: VIEWS,
                                                                                                }}
                                                                                            >
                                                                                                <MarkedLocationIdContext.Provider
                                                                                                    value={[
                                                                                                        markedLocationId,
                                                                                                        setMarkedLocationId,
                                                                                                    ]}
                                                                                                >
                                                                                                    <StartingLocationIdContext.Provider
                                                                                                        value={props.startingLocationId}
                                                                                                    >
                                                                                                        <GridActiveLocationIdContext.Provider
                                                                                                            value={[
                                                                                                                gridActiveLocationId,
                                                                                                                setGridActiveLocationId,
                                                                                                            ]}
                                                                                                        >
                                                                                                            <LoadedLocationsMapContext.Provider
                                                                                                                value={[
                                                                                                                    loadedLocationsMap,
                                                                                                                    dispatchLoadedLocationsAction,
                                                                                                                ]}
                                                                                                            >
                                                                                                                <RootLocationIdContext.Provider
                                                                                                                    value={
                                                                                                                        props.rootLocationId
                                                                                                                    }
                                                                                                                >
                                                                                                                    <SelectedItemsContext.Provider
                                                                                                                        value={{
                                                                                                                            selectedItems,
                                                                                                                            dispatchSelectedItemsAction,
                                                                                                                        }}
                                                                                                                    >
                                                                                                                        <SelectedLocationsContext.Provider
                                                                                                                            value={[
                                                                                                                                selectedLocations,
                                                                                                                                dispatchSelectedLocationsAction,
                                                                                                                            ]}
                                                                                                                        >
                                                                                                                            <CreateContentWidgetContext.Provider
                                                                                                                                value={[
                                                                                                                                    createContentVisible,
                                                                                                                                    setCreateContentVisible,
                                                                                                                                ]}
                                                                                                                            >
                                                                                                                                <SuggestionsStorageContext.Provider
                                                                                                                                    value={[
                                                                                                                                        suggestionsStorage,
                                                                                                                                        setSuggestionsStorage,
                                                                                                                                    ]}
                                                                                                                                >
                                                                                                                                    <ContentOnTheFlyDataContext.Provider
                                                                                                                                        value={[
                                                                                                                                            contentOnTheFlyData,
                                                                                                                                            setContentOnTheFlyData,
                                                                                                                                        ]}
                                                                                                                                    >
                                                                                                                                        <ContentOnTheFlyConfigContext.Provider
                                                                                                                                            value={
                                                                                                                                                props.contentOnTheFly
                                                                                                                                            }
                                                                                                                                        >
                                                                                                                                            <EditOnTheFlyDataContext.Provider
                                                                                                                                                value={[
                                                                                                                                                    editOnTheFlyData,
                                                                                                                                                    setEditOnTheFlyData,
                                                                                                                                                ]}
                                                                                                                                            >
                                                                                                                                                <SearchTextContext.Provider
                                                                                                                                                    value={[
                                                                                                                                                        searchText,
                                                                                                                                                        setSearchText,
                                                                                                                                                        makeSearch,
                                                                                                                                                    ]}
                                                                                                                                                >
                                                                                                                                                    <DropdownPortalRefContext.Provider
                                                                                                                                                        value={
                                                                                                                                                            dropdownPortalRef
                                                                                                                                                        }
                                                                                                                                                    >
                                                                                                                                                        <Tab />
                                                                                                                                                    </DropdownPortalRefContext.Provider>
                                                                                                                                                </SearchTextContext.Provider>
                                                                                                                                            </EditOnTheFlyDataContext.Provider>
                                                                                                                                        </ContentOnTheFlyConfigContext.Provider>
                                                                                                                                    </ContentOnTheFlyDataContext.Provider>
                                                                                                                                </SuggestionsStorageContext.Provider>
                                                                                                                            </CreateContentWidgetContext.Provider>
                                                                                                                        </SelectedLocationsContext.Provider>
                                                                                                                    </SelectedItemsContext.Provider>
                                                                                                                </RootLocationIdContext.Provider>
                                                                                                            </LoadedLocationsMapContext.Provider>
                                                                                                        </GridActiveLocationIdContext.Provider>
                                                                                                    </StartingLocationIdContext.Provider>
                                                                                                </MarkedLocationIdContext.Provider>
                                                                                            </ViewContext.Provider>
                                                                                        </CurrentViewContext.Provider>
                                                                                    </SortOrderContext.Provider>
                                                                                </SortingContext.Provider>
                                                                            </ConfirmContext.Provider>
                                                                        </CancelContext.Provider>
                                                                    </TitleContext.Provider>
                                                                </TabsConfigContext.Provider>
                                                            </TabsContext.Provider>
                                                        </ActiveTabContext.Provider>
                                                    </SnackbarActionsContext.Provider>
                                                </AllowedContentTypesContext.Provider>
                                            </ContainersOnlyContext.Provider>
                                        </MultipleConfigContext.Provider>
                                    </ContentTypesMapContext.Provider>
                                </ContentTypesInfoMapContext.Provider>
                            </AllowConfirmationContext.Provider>
                        </AllowRedirectsContext.Provider>
                    </BlockFetchLocationHookContext.Provider>
                </RestInfoContext.Provider>
            </UDWContext.Provider>
        </div>
    );
    /* eslint-enable max-len */
};

UniversalDiscoveryModule.propTypes = {
    onConfirm: PropTypes.func.isRequired,
    onCancel: PropTypes.func,
    title: PropTypes.string.isRequired,
    activeTab: PropTypes.string,
    rootLocationId: PropTypes.number,
    startingLocationId: PropTypes.number,
    multiple: PropTypes.bool,
    multipleItemsLimit: PropTypes.number,
    containersOnly: PropTypes.bool,
    allowedContentTypes: PropTypes.array.isRequired,
    activeSortClause: PropTypes.string,
    activeSortOrder: PropTypes.string,
    activeView: PropTypes.string,
    contentOnTheFly: PropTypes.shape({
        allowedLanguages: PropTypes.array.isRequired,
        allowedLocations: PropTypes.array.isRequired,
        preselectedLocation: PropTypes.string.isRequired,
        preselectedContentType: PropTypes.string.isRequired,
        hidden: PropTypes.bool.isRequired,
        autoConfirmAfterPublish: PropTypes.bool.isRequired,
    }).isRequired,
    tabsConfig: PropTypes.objectOf(
        PropTypes.shape({
            itemsPerPage: PropTypes.number.isRequired,
            priority: PropTypes.number.isRequired,
            hidden: PropTypes.bool.isRequired,
        }),
    ).isRequired,
    selectedLocations: PropTypes.array,
    allowRedirects: PropTypes.bool.isRequired,
    allowConfirmation: PropTypes.bool.isRequired,
    restInfo: PropTypes.shape({
        token: PropTypes.string,
        siteaccess: PropTypes.string,
        accsessToken: PropTypes.string,
        instanceUrl: PropTypes.string,
    }),
    snackbarEnabledActions: PropTypes.array,
};

UniversalDiscoveryModule.defaultProps = {
    onCancel: null,
    activeTab: 'browse',
    rootLocationId: 1,
    startingLocationId: null,
    multiple: false,
    multipleItemsLimit: 1,
    containersOnly: false,
    activeSortClause: 'date',
    activeSortOrder: 'ascending',
    activeView: 'finder',
    selectedLocations: [],
    restInfo: defaultRestInfo,
    snackbarEnabledActions: Object.values(SNACKBAR_ACTIONS),
};

export default UniversalDiscoveryModule;
