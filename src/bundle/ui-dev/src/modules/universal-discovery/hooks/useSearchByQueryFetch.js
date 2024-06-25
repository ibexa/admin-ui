import { useContext, useCallback, useReducer } from 'react';

import { findLocationsBySearchQuery } from '../services/universal.discovery.service';
import { RestInfoContext, LoadedLocationsMapContext } from '../universal.discovery.module';

const SEARCH_START = 'SEARCH_START';
const SEARCH_END = 'SEARCH_END';

const startSearch = () => ({ isLoading: true, data: {} });
const endSearch = ({ response }) => ({ isLoading: false, data: response });

const searchByQueryReducer = (state, action) => {
    switch (action.type) {
        case SEARCH_START:
            return startSearch();
        case SEARCH_END:
            return endSearch(action);
        default:
            throw new Error();
    }
};

export const useSearchByQueryFetch = () => {
    const restInfo = useContext(RestInfoContext);
    const [, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [{ isLoading, data }, dispatch] = useReducer(searchByQueryReducer, { isLoading: false, data: {} });

    const searchByQuery = useCallback(
        (
            searchText,
            contentTypesIdentifiers,
            sectionIdentifier,
            subtreePathString,
            limit,
            offset,
            languageCode,
            sortClause = null,
            sortOrder = null,
            imageCriterionData = null,
            aggregations = {},
            filters = {},
            fullTextCriterion = null,
            contentNameCriterion = null,
        ) => {
            const handleFetch = (response) => {
                dispatchLoadedLocationsAction({ type: 'CLEAR_LOCATIONS' });
                dispatch({ type: SEARCH_END, response });
            };
            const query = {};

            if (searchText) {
                query.FullTextCriterion = `${searchText}*`;
            }

            if (fullTextCriterion) {
                query.FullTextCriterion = fullTextCriterion;
            }

            if (contentNameCriterion) {
                query.ContentNameCriterion = contentNameCriterion;
            }

            if (contentTypesIdentifiers && contentTypesIdentifiers.length) {
                query.ContentTypeIdentifierCriterion = contentTypesIdentifiers;
            }

            if (sectionIdentifier) {
                query.SectionIdentifierCriterion = sectionIdentifier;
            }

            if (subtreePathString) {
                query.SubtreeCriterion = subtreePathString;
            }

            const isImageCriterionDataEmpty = !imageCriterionData || Object.keys(imageCriterionData).length === 0;

            if (!isImageCriterionDataEmpty) {
                const imageCriterion = {
                    fieldDefIdentifier: 'image',
                    ...imageCriterionData,
                };

                query.ImageCriterion = imageCriterion;
            }

            dispatch({ type: SEARCH_START });
            return findLocationsBySearchQuery(
                { ...restInfo, query, aggregations, filters, sortClause, sortOrder, limit, offset, languageCode },
                handleFetch,
            );
        },
        [restInfo, dispatch],
    );

    return [isLoading, data, searchByQuery];
};
