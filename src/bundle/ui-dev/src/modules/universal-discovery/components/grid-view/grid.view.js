import React, { useContext, useState, useEffect, createContext } from 'react';
import PropTypes from 'prop-types';

import GridViewItem from './grid.view.item';
import Breadcrumbs from '../breadcrumbs/breadcrumbs';

import { useFindLocationsByParentLocationIdFetch } from '../../hooks/useFindLocationsByParentLocationIdFetch';
import {
    SORTING_OPTIONS,
    LoadedLocationsMapContext,
    SortingContext,
    SortOrderContext,
    MarkedLocationIdContext,
} from '../../universal.discovery.module';

export const ActiveLocationIdContext = createContext();

const SCROLL_OFFSET = 200;

const GridView = ({ itemsPerPage }) => {
    const [offset, setOffset] = useState(0);
    const [markedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [sorting] = useContext(SortingContext);
    const [sortOrder] = useContext(SortOrderContext);
    const [activeLocationId, setActiveLocationId] = useState(markedLocationId ?? loadedLocationsMap[0]?.parentLocationId);
    const sortingOptions = SORTING_OPTIONS.find((option) => option.sortClause === sorting);
    const locationData = loadedLocationsMap.find(({ parentLocationId }) => parentLocationId === activeLocationId);
    const locationDataToLoad = loadedLocationsMap.length ? loadedLocationsMap[loadedLocationsMap.length - 1] : { subitems: [] };
    const [loadedLocations, isLoading] = useFindLocationsByParentLocationIdFetch(
        locationDataToLoad,
        { sortClause: sortingOptions.sortClause, sortOrder },
        itemsPerPage,
        offset,
        true,
    );
    const loadMore = ({ target }) => {
        const areAllItemsLoaded = locationData.subitems.length >= loadedLocations.totalCount;
        const isOffsetReached = target.scrollHeight - target.clientHeight - target.scrollTop < SCROLL_OFFSET;

        if (areAllItemsLoaded || !isOffsetReached || isLoading) {
            return;
        }

        setOffset(offset + itemsPerPage);
    };
    const renderItem = (itemData) => {
        if (!itemData.version) {
            return null;
        }

        return <GridViewItem key={itemData.location.id} location={itemData.location} version={itemData.version} />;
    };

    useEffect(() => {
        if (isLoading || !loadedLocations.subitems) {
            return;
        }

        const data = { ...locationData, ...loadedLocations, subitems: [...locationData.subitems, ...loadedLocations.subitems] };

        setOffset(0);
        dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data });
    }, [loadedLocations, dispatchLoadedLocationsAction, isLoading]);

    return (
        <ActiveLocationIdContext.Provider value={[activeLocationId, setActiveLocationId]}>
            <div className="c-grid">
                <Breadcrumbs />
                <div className="ibexa-grid-view c-grid__items-wrapper" onScroll={loadMore}>
                    {locationData.subitems.map(renderItem)}
                </div>
            </div>
        </ActiveLocationIdContext.Provider>
    );
};

GridView.propTypes = {
    itemsPerPage: PropTypes.number,
};

GridView.defaultProps = {
    itemsPerPage: 50,
};

export default GridView;
