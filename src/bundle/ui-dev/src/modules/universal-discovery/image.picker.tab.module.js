import React, { useContext, useState, useEffect, useRef } from 'react';
const { Translator, ibexa } = window;

const ImagePickerTabModule = () => {
    // const shouldRestorePreviousStateRef = useRef(true);
    // const restInfo = useContext(RestInfoContext);
    // const tabsConfig = useContext(TabsConfigContext);
    // const [currentView] = useContext(CurrentViewContext);
    // const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    // const [sorting] = useContext(SortingContext);
    // const [sortOrder] = useContext(SortOrderContext);
    // const rootLocationId = useContext(RootLocationIdContext);
    // const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    // const [bookmarkedLocationMarked, setBookmarkedLocationMarked] = useState(null);
    // const views = {
    //     grid: <GridView itemsPerPage={tabsConfig.bookmarks.itemsPerPage} />,
    //     finder: <Finder itemsPerPage={tabsConfig.bookmarks.itemsPerPage} />,
    //     tree: <TreeView itemsPerPage={tabsConfig.bookmarks.itemsPerPage} />,
    // };
    // const renderBrowseLocations = () => {
    //     if (!markedLocationId) {
    //         return null;
    //     }

    //     return views[currentView];
    // };

    // useEffect(() => {
    //     setMarkedLocationId(null);
    //     dispatchLoadedLocationsAction({ type: 'CLEAR_LOCATIONS' });

    //     return () => {
    //         if (shouldRestorePreviousStateRef.current) {
    //             setMarkedLocationId(markedLocationId);
    //             dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: loadedLocationsMap });
    //         }
    //     };
    // }, []);

    // useEffect(() => {
    //     if (!bookmarkedLocationMarked) {
    //         return;
    //     }

    //     shouldRestorePreviousStateRef.current = false;
    //     setMarkedLocationId(bookmarkedLocationMarked);
    //     loadAccordionData(
    //         {
    //             ...restInfo,
    //             parentLocationId: bookmarkedLocationMarked,
    //             sortClause: sorting,
    //             sortOrder: sortOrder,
    //             gridView: currentView === 'grid',
    //             rootLocationId,
    //         },
    //         (locationsMap) => {
    //             dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: locationsMap });
    //         },
    //     );
    // }, [bookmarkedLocationMarked, currentView, restInfo, dispatchLoadedLocationsAction, setMarkedLocationId]);

    // useEffect(() => {
    //     if (markedLocationId !== bookmarkedLocationMarked) {
    //         dispatchLoadedLocationsAction({ type: 'CUT_LOCATIONS', locationId: markedLocationId });
    //         setBookmarkedLocationMarked(null);
    //     }
    // }, [markedLocationId, setBookmarkedLocationMarked, bookmarkedLocationMarked, dispatchLoadedLocationsAction]);

    return (
       
    );
};

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.tabs',
    [
        {
            id: 'image_picker',
            component: ImagePickerTabModule,
            label: Translator.trans(/*@Desc("Image Picker")*/ 'image_picker.label', {}, 'ibexa_universal_discovery_widget'),
            icon: ibexa.helpers.icon.getIconPath('bookmark'),
        },
    ],
    true,
);

export default ImagePickerTabModule;
