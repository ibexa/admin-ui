import { useEffect, useContext, useReducer } from 'react';

import { findLocationsByParentLocationId } from '../services/universal.discovery.service';
import { RestInfoContext, BlockFetchLocationHookContext } from '../universal.discovery.module';

const fetchInitialState = {
    dataFetched: false,
    data: {},
};

const fetchReducer = (state, action) => {
    switch (action.type) {
        case 'FETCH_START':
            return fetchInitialState;
        case 'FETCH_END':
            return { data: action.data, dataFetched: true };
        default:
            throw new Error();
    }
};

export const useFindLocationsByParentLocationIdFetch = (locationData, { sortClause, sortOrder }, limit, offset, gridView = false, isPaginated = false) => {
    const restInfo = useContext(RestInfoContext);
    const [isFetchLocationHookBlocked] = useContext(BlockFetchLocationHookContext);
    const [state, dispatch] = useReducer(fetchReducer, fetchInitialState);

    useEffect(() => {
        console.log('started')
        if (isFetchLocationHookBlocked) {
            return;
        }

        let effectCleaned = false;

        console.log('started 1')
        if (
            !locationData.parentLocationId ||
            locationData.collapsed ||
            locationData.subitems.length >= locationData.totalCount ||
            (locationData.subitems.length >= limit + offset && !isPaginated)
        ) {
            dispatch({ type: 'FETCH_END', data: {} });

            return;
        }
        console.log('started 2')
        dispatch({ type: 'FETCH_START' });
        findLocationsByParentLocationId(
            {
                ...restInfo,
                parentLocationId: locationData.parentLocationId,
                sortClause,
                sortOrder,
                limit,
                offset,
                gridView,
            },
            (response) => {
                // console.log(response, effectCleaned)
                if (effectCleaned) {
                    return;
                }

                dispatch({ type: 'FETCH_END', data: response });
            },
        );

        return () => {
            effectCleaned = true;
        };
    }, [
        restInfo,
        sortClause,
        sortOrder,
        locationData.parentLocationId,
        locationData.subitems.length,
        limit,
        offset,
        gridView,
        locationData.collapsed,
        isFetchLocationHookBlocked,
    ]);

    if (isFetchLocationHookBlocked) {
        return [{}, true];
    }

    return [state.data, !state.dataFetched];
};
