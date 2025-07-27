import { useEffect, useContext, useReducer, useRef } from 'react';

import { loadBookmarks } from '../services/universal.discovery.service';
import { RestInfoContext } from '../universal.discovery.module';

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

export const useLoadBookmarksFetch = (limit, offset) => {
    const effectCleanedRef = useRef(false);
    const restInfo = useContext(RestInfoContext);
    const [state, dispatch] = useReducer(fetchReducer, fetchInitialState);
    const reload = () => {
        effectCleanedRef.current = false;

        dispatch({ type: 'FETCH_START' });
        loadBookmarks(
            {
                ...restInfo,
                limit,
                offset,
            },
            (response) => {
                if (effectCleanedRef.current) {
                    return;
                }

                dispatch({ type: 'FETCH_END', data: response });
            },
        );
    };

    useEffect(() => {
        effectCleanedRef.current = false;

        dispatch({ type: 'FETCH_START' });
        loadBookmarks(
            {
                ...restInfo,
                limit,
                offset,
            },
            (response) => {
                if (effectCleanedRef.current) {
                    return;
                }

                dispatch({ type: 'FETCH_END', data: response });
            },
        );

        return () => {
            effectCleanedRef.current = true;
        };
    }, [restInfo, limit, offset]);

    return [state.data, !state.dataFetched, reload];
};
