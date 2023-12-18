import { useEffect, useContext, useReducer } from 'react';

import { getContentTypeDataByHref } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/content.type.helper';
import { findLocationsByParentLocationId, findSuggestions } from '../services/universal.discovery.service';
import { RestInfoContext, BlockFetchLocationHookContext, SuggestionsStorageContext } from '../universal.discovery.module';

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

export const useFindLocationsByParentLocationIdFetch = (locationData, { sortClause, sortOrder }, limit, offset, gridView = false) => {
    const restInfo = useContext(RestInfoContext);
    const [isFetchLocationHookBlocked] = useContext(BlockFetchLocationHookContext);
    const [suggestionsStorage, setSuggestionsStorage] = useContext(SuggestionsStorageContext);
    const [state, dispatch] = useReducer(fetchReducer, fetchInitialState);
    const getFindLocationsPromise = () =>
        new Promise((resolve) => {
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
                resolve,
            );
        });
    const getFindSuggestionsPromise = () =>
        new Promise((resolve) => {
            if (suggestionsStorage[locationData.parentLocationId]) {
                resolve(suggestionsStorage[locationData.parentLocationId]);

                return;
            }

            findSuggestions(
                {
                    ...restInfo,
                    parentLocationId: locationData.parentLocationId,
                },
                resolve,
            );
        });

    useEffect(() => {
        if (isFetchLocationHookBlocked) {
            return;
        }

        let effectCleaned = false;

        if (
            !locationData.parentLocationId ||
            locationData.collapsed ||
            locationData.subitems.length >= locationData.totalCount ||
            locationData.subitems.length >= limit + offset
        ) {
            dispatch({ type: 'FETCH_END', data: {} });

            return;
        }

        dispatch({ type: 'FETCH_START' });
        Promise.all([getFindLocationsPromise(), getFindSuggestionsPromise()]).then(([locations, suggestions]) => {
            if (effectCleaned) {
                return;
            }

            const suggestionsResults = suggestions.View?.Result.aggregations[0]?.entries.map(({ key }) => ({
                data: getContentTypeDataByHref(key.ContentType._href),
            }));

            if (suggestionsResults) {
                setSuggestionsStorage((prevState) => ({
                    ...prevState,
                    [locationData.parentLocationId]: suggestionsResults,
                }));
            }

            dispatch({ type: 'FETCH_END', data: locations });
        });

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
