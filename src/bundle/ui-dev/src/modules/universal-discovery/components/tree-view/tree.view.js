import React, { useContext, useMemo } from 'react';
import PropTypes from 'prop-types';

import { getId as getUserId } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/user.helper';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

import ContentTreeModule from '../../../content-tree/content.tree.module';
import { loadAccordionData } from '../../services/universal.discovery.service';
import { getLocationData } from '../../content.meta.preview.module';
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
    SortOrderContext,
    SortingContext,
} from '../../universal.discovery.module';
import { getAdminUiConfig } from '../../../modules.service';

const TreeView = ({ itemsPerPage }) => {
    const adminUiConfig = getAdminUiConfig();
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [multiple] = useContext(MultipleConfigContext);
    const [, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [sortOrder] = useContext(SortOrderContext);
    const [sorting] = useContext(SortingContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const restInfo = useContext(RestInfoContext);
    const rootLocationId = useContext(RootLocationIdContext);
    const locationData = useMemo(() => getLocationData(loadedLocationsMap, markedLocationId), [markedLocationId, loadedLocationsMap]);
    const userId = adminUiConfig.userId ?? getUserId();
    const expandItem = (item, event) => {
        event.preventDefault();
        event.currentTarget.closest('.c-list-item__row').querySelector('.c-list-item__toggler').click();
    };
    const markLocation = (item) => {
        const { locationId } = item;

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
    const readSubtreeRecursive = (tree) => {
        if (tree.length === 0) {
            return [];
        }

        const location = tree.shift();

        return [
            {
                children: readSubtreeRecursive(tree),
                limit: itemsPerPage,
                locationId: location.parentLocationId,
                offset: 0,
                '_media-type': 'application/vnd.ibexa.api.ContentTreeLoadSubtreeRequestNode',
            },
        ];
    };
    const readSubtree = () => readSubtreeRecursive([...loadedLocationsMap]);
    const currentLocationPath = locationData && locationData.location ? locationData.location.pathString : '/1/';
    const locationsLoaded = loadedLocationsMap.length > 1 || (loadedLocationsMap.length === 1 && loadedLocationsMap[0].subitems.length > 0);
    const contentTreeVisible = (markedLocationId !== null && locationsLoaded) || markedLocationId === null;
    const className = createCssClassNames({
        'c-tree': true,
        'c-tree--single-select': !multiple,
    });

    return (
        <div className={className}>
            {contentTreeVisible && (
                <ContentTreeModule
                    userId={userId}
                    currentLocationPath={currentLocationPath}
                    rootLocationId={rootLocationId}
                    subitemsLimit={adminUiConfig.contentTree.childrenLoadMaxLimi}
                    subitemsLoadLimit={adminUiConfig.contentTree.loadMoreLimit}
                    treeMaxDepth={adminUiConfig.contentTree.treeMaxDepth}
                    restInfo={restInfo}
                    onClickItem={expandItem}
                    readSubtree={readSubtree}
                    afterItemToggle={markLocation}
                    sort={{
                        sortClause: sorting,
                        sortOrder,
                    }}
                    resizable={false}
                />
            )}
        </div>
    );
};

TreeView.propTypes = {
    itemsPerPage: PropTypes.number,
};

TreeView.defaultProps = {
    itemsPerPage: 50,
};

export default TreeView;
