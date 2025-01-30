import { useEffect, useContext, useReducer } from 'react';

import { RestInfoContext } from '../universal.discovery.module';

const fetchInitialState = {
    isLoading: false,
    data: null,
    pageIndex: 0,
};

const FETCH_START = 'FETCH_START';
const FETCH_END = 'FETCH_END';
const CHANGE_PAGE = 'CHANGE_PAGE';

const fetchReducer = (state, action) => {
    switch (action.type) {
        case FETCH_START:
            return {
                ...state,
                data: null,
                isLoading: true,
            };
        case FETCH_END:
            return { ...state, data: action.data, isLoading: false };
        case CHANGE_PAGE: {
            const isCurrentPageIndex = action.pageIndex === state.pageIndex;

            if (isCurrentPageIndex) {
                return state;
            }

            return {
                ...state,
                data: null,
                pageIndex: action.pageIndex,
            };
        }
        default:
            throw new Error();
    }
};

export const usePaginableFetch = ({ restInfo, itemsPerPage, extraFetchParams }, fetchFunction) => {
    const restInfoData = restInfo ?? useContext(RestInfoContext);
    const [state, dispatch] = useReducer(fetchReducer, fetchInitialState);
    const changePage = (pageIndex) => dispatch({ type: CHANGE_PAGE, pageIndex });

    useEffect(() => {
        dispatch({ type: FETCH_START });

        const offset = state.pageIndex * itemsPerPage;
        const { abortController } = fetchFunction({ ...restInfoData, limit: itemsPerPage, offset, ...extraFetchParams }, (data) =>
            dispatch({ type: FETCH_END, data }),
        );

        return () => {
            if (abortController) {
                abortController.abort();
            }
        };
    }, [state.pageIndex, restInfoData, itemsPerPage, extraFetchParams]);

    return [state.data, state.isLoading, state.pageIndex, changePage];
};
