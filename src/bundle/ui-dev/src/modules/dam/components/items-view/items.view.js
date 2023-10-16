import React, { useContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import ItemsViewTopBar from './items.view.top.bar';
import Grid from '../grid-view/grid.view';
import List from '../list-view/list.view';
import { SelectedItemsContext } from '../../image.picker.tab.module';
import Pagination from '../../../common/pagination/pagination';
import { useFindLocationsByParentLocationIdFetch } from '../../../universal-discovery/hooks/useFindLocationsByParentLocationIdFetch';
import {
    ContentTypesMapContext,
    LoadedLocationsMapContext,
    MultipleConfigContext,
    SORTING_OPTIONS,
    SelectedLocationsContext,
    SortOrderContext,
    SortingContext,
} from '../../../universal-discovery/universal.discovery.module';
import PaginationInfo from '../../../common/pagination/pagination.info';
import Icon from '../../../common/icon/icon';

const { ibexa } = window;

export const GRID_VIEW = 'GRID_VIEW';
export const LIST_VIEW = 'LIST_VIEW';

const ItemsView = () => {
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const checkIsSelectable = (location) => true;
    const checkIsSelected = (location) => selectedLocations.some((selectedLocation) => selectedLocation.location.id === location.id);
    const toggleSelectedLocation = (location) => {
        if (!checkIsSelectable(location)) {
            return;
        }

        const isSelected = checkIsSelected(location);

        if (isSelected) {
            dispatchSelectedLocationsAction({ type: 'REMOVE_SELECTED_LOCATION', id: location.id });
        } else {
            if (!multiple) {
                dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });
            }

            dispatchSelectedLocationsAction({ type: 'ADD_SELECTED_LOCATION', location });
        }
    };

    const itemsPerPage = 3;
    const contentTypesMap = useContext(ContentTypesMapContext);
    const [offset, setOffset] = useState(0);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [sorting] = useContext(SortingContext);
    const [sortOrder] = useContext(SortOrderContext);
    const sortingOptions = SORTING_OPTIONS.find((option) => option.sortClause === sorting);
    const locationData = loadedLocationsMap.length ? loadedLocationsMap[loadedLocationsMap.length - 1] : { subitems: [] };
    const [loadedLocations, isLoading] = useFindLocationsByParentLocationIdFetch(
        locationData,
        { sortClause: sortingOptions.sortClause, sortOrder },
        itemsPerPage,
        offset,
        true,
        true,
    );

    const [activeView, setActiveView] = useState(GRID_VIEW);

    useEffect(() => {
        setOffset(0);
    }, [locationData.location]);

    useEffect(() => {
        // console.log('useE', isLoading, loadedLocations.subitems);
        if (isLoading || !loadedLocations.subitems) {
            return;
        }

        // const data = { ...locationData, ...loadedLocations, subitems: [...locationData.subitems, ...loadedLocations.subitems] };
        const data = { ...locationData, ...loadedLocations, subitems: [...loadedLocations.subitems] };

        // setOffset(0);
        dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data });
    }, [loadedLocations, dispatchLoadedLocationsAction, isLoading]);

    // console.log('loadedLocations', locationData);

    // if (!(loadedLocations?.subitems ?? null)) {
    //     return null;
    // }

    const gridItems = locationData.subitems
        .filter((item) => !!item.version)
        .map((item) => {
            // const contentTypeIdentifier = item.Content._info.contentType.identifier;
            return {
                itemId: item.location.id,
                thumbnail: item.version.Thumbnail,
                iconPath: contentTypesMap[item.location.ContentInfo.Content.ContentType._href].thumbnail,
                title: item.location.ContentInfo.Content.TranslatedName,
                // detailA: ibexa.helpers.contentType.getContentTypeName(contentTypeIdentifier),
                isSelected: checkIsSelected(item.location),
                onClick: () => toggleSelectedLocation(item.location),
            };
        });
    const listItems = locationData.subitems
        .filter((item) => !!item.version)
        .map((item) => ({
            ...item,
            thumbnail: item.version.Thumbnail,
            iconPath: contentTypesMap[item.location.ContentInfo.Content.ContentType._href].thumbnail,
            isSelected: checkIsSelected(item.location),
            onClick: () => toggleSelectedLocation(item.location),
        }));
    const changePage = (pageIndex) => {
        const data = { ...locationData, subitems: [] };

        // setOffset(0);
        dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data });
        setOffset(pageIndex * itemsPerPage);
    };

    // console.log(loadedLocations, locationData, gridItems, offset);
    return (
        <div className="c-dwb-items-view">
            <ItemsViewTopBar title={'TODO'} activeView={activeView} onViewChange={setActiveView} />
            <div className="c-dwb-items-view__container">
                {activeView === GRID_VIEW && !isLoading && <Grid items={gridItems} />}
                {activeView === LIST_VIEW && !isLoading && <List items={listItems} />}
                {isLoading && (
                    <div className="c-dwb-items-view__loading-spinner">
                        <Icon name="spinner" extraClasses="ibexa-icon--medium ibexa-spin" />
                    </div>
                )}
                <div className="c-dwb-items-view__pagination">
                    {!isLoading && (
                        <>
                            <PaginationInfo totalCount={locationData.totalCount} viewingCount={gridItems.length} />
                            <Pagination
                                proximity={1}
                                itemsPerPage={itemsPerPage}
                                activePageIndex={offset / itemsPerPage}
                                totalCount={locationData.totalCount}
                                onPageChange={changePage}
                                disabled={false}
                            />
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

ItemsView.propTypes = {};

ItemsView.defaultProps = {};

export default ItemsView;
