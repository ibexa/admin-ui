import React, { useContext, useEffect, useMemo, useRef } from 'react';

import { getId as getUserId } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/user.helper';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { findMarkedLocation } from '../../helpers/locations.helper';

import { findLocationsById, loadAccordionData } from '../../services/universal.discovery.service';
import {
    AllowedContentTypesContext,
    ContainersOnlyContext,
    ContentTypesMapContext,
    LoadedLocationsMapContext,
    MarkedLocationIdContext,
    MultipleConfigContext,
    RestInfoContext,
    RootLocationIdContext,
    SelectedLocationsContext,
    SelectionConfigContext,
    SortOrderContext,
    SortingContext,
} from '../../universal.discovery.module';
import { getAdminUiConfig } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const { ibexa, document } = window;

const CONTENT_TREE_MODULE_ID = 'ibexa-udw-content-tree';

const TreeView = () => {
    const adminUiConfig = getAdminUiConfig();
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [multiple, multipleItemsLimit] = useContext(MultipleConfigContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const { isInitLocationsDeselectionBlocked, initSelectedLocationsIds } = useContext(SelectionConfigContext);
    const [sortOrder] = useContext(SortOrderContext);
    const [sorting] = useContext(SortingContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const restInfo = useContext(RestInfoContext);
    const rootLocationId = useContext(RootLocationIdContext);
    const locationData = useMemo(() => findMarkedLocation(loadedLocationsMap, markedLocationId), [markedLocationId, loadedLocationsMap]);
    const selectedLocationsIds = useMemo(() => selectedLocations.map(({ location }) => location.id), [selectedLocations]);
    const selectedLocationsIdsRef = useRef(selectedLocationsIds);
    const userId = getUserId();
    const expandItem = (item, event) => {
        event.preventDefault();
        event.currentTarget.closest('.c-tb-list-item-single__element').querySelector('.c-tb-toggler').click();
    };
    const markLocation = (item) => {
        const { locationId } = item.internalItem;

        if (locationId === markedLocationId) {
            return;
        }

        loadAccordionData(
            {
                ...restInfo,
                parentLocationId: locationId,
                sortClause: sorting,
                sortOrder: sortOrder,
                rootLocationId,
            },
            (locationsMap) => {
                const { location } = locationsMap[locationsMap.length - 1];
                const contentTypeInfo = contentTypesMap[location.ContentInfo.Content.ContentType._href];
                const { isContainer } = contentTypeInfo;
                const isNotSelectable =
                    (containersOnly && !isContainer) || (allowedContentTypes && !allowedContentTypes.includes(contentTypeInfo.identifier));

                setMarkedLocationId(locationId);
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: locationsMap });

                if (!multiple) {
                    dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });

                    if (!isNotSelectable) {
                        dispatchSelectedLocationsAction({ type: 'REPLACE_SELECTED_LOCATIONS', locations: [{ location }] });
                    }
                }
            },
        );
    };
    const handleItemClick = (item, event) => {
        markLocation(item);
        expandItem(item, event);
    };
    const checkIsInputDisabled = (item, { isSelected }) => {
        const { locationId, isContainer, contentTypeIdentifier } = item.internalItem;
        const isNotSelectable =
            (containersOnly && !isContainer) || (allowedContentTypes && !allowedContentTypes.includes(contentTypeIdentifier));
        const isDeselectionBlocked = isSelected && initSelectedLocationsIds.includes(locationId) && isInitLocationsDeselectionBlocked;

        return isNotSelectable || isDeselectionBlocked;
    };
    const currentLocationPath = locationData && locationData.location ? locationData.location.pathString : '/1/';
    const locationsLoaded = loadedLocationsMap.length > 1 || (loadedLocationsMap.length === 1 && loadedLocationsMap[0].subitems.length > 0);
    const contentTreeVisible = (markedLocationId !== null && locationsLoaded) || markedLocationId === null;
    const className = createCssClassNames({
        'c-tree': true,
        'c-tree--single-select': !multiple,
    });

    selectedLocationsIdsRef.current = selectedLocationsIds;

    useEffect(() => {
        const updateSelectedLocations = ({ detail }) => {
            if (detail.id !== CONTENT_TREE_MODULE_ID) {
                return;
            }

            const treeSelectedIds = detail.items.map(({ id }) => id);
            const removedIds = selectedLocationsIdsRef.current.filter((id) => !treeSelectedIds.includes(id));
            const addedIds = treeSelectedIds.filter((id) => !selectedLocationsIdsRef.current.includes(id));

            removedIds.forEach((id) => dispatchSelectedLocationsAction({ type: 'REMOVE_SELECTED_LOCATION', id }));

            if (addedIds.length) {
                findLocationsById({ ...restInfo, id: addedIds.join(',') }, (locations) => {
                    const notSelectedLocations = locations.filter(({ id }) => !selectedLocationsIdsRef.current.includes(id));

                    dispatchSelectedLocationsAction({ type: 'ADD_SELECTED_LOCATIONS', locations: notSelectedLocations });
                });
            }
        };

        document.body.addEventListener('ibexa-tb-update-selected', updateSelectedLocations, false);

        return () => {
            document.body.removeEventListener('ibexa-tb-update-selected', updateSelectedLocations, false);
        };
    }, [restInfo, dispatchSelectedLocationsAction]);

    return (
        <div className={className}>
            {contentTreeVisible && (
                <ibexa.modules.ContentTree
                    moduleId={CONTENT_TREE_MODULE_ID}
                    userId={userId}
                    currentLocationPath={currentLocationPath}
                    rootLocationId={rootLocationId}
                    subitemsLimit={adminUiConfig.contentTree.childrenLoadMaxLimit}
                    subitemsLoadLimit={adminUiConfig.contentTree.loadMoreLimit}
                    treeMaxDepth={adminUiConfig.contentTree.treeMaxDepth}
                    restInfo={restInfo}
                    onClickItem={handleItemClick}
                    sort={{
                        sortClause: sorting,
                        sortOrder,
                    }}
                    isResizable={false}
                    headerVisible={false}
                    actionsVisible={false}
                    linksDisabled={true}
                    isLocalStorageActive={false}
                    useTheme={false}
                    selectionDisabled={!multiple}
                    selectedLimit={multiple && multipleItemsLimit ? multipleItemsLimit : null}
                    checkIsInputDisabled={checkIsInputDisabled}
                    initiallySelectedItemsIds={selectedLocationsIds}
                />
            )}
        </div>
    );
};

export default TreeView;
