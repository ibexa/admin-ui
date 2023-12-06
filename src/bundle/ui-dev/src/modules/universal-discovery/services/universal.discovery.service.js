import { getRequestHeaders, getRequestMode } from '../../../../../Resources/public/js/scripts/helpers/request.helper.js';
import { showErrorNotification } from '../../common/services/notification.service';
import { handleRequestResponse, handleRequestResponseStatus } from '../../common/helpers/request.helper.js';

const DEFAULT_INSTANCE_URL = window.location.origin;
const HEADERS_CREATE_VIEW = {
    Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
    'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
};
const ENDPOINT_CREATE_VIEW = '/api/ibexa/v2/views';
const ENDPOINT_BOOKMARK = '/api/ibexa/v2/bookmark';
const ENDPOINT_LOCATION = '/api/ibexa/v2/module/universal-discovery/location';
const ENDPOINT_ACCORDION = '/api/ibexa/v2/module/universal-discovery/accordion';
const ENDPOINT_LOCATION_LIST = '/api/ibexa/v2/module/universal-discovery/locations';

export const QUERY_LIMIT = 50;

const showErrorNotificationAbortWrapper = (error) => {
    if (error?.name === 'AbortError') {
        return;
    }

    return showErrorNotification(error);
};

const mapSubitems = (subitems) => {
    return subitems.locations.map((location) => {
        const mappedSubitems = {
            location: location.Location,
        };

        if (subitems.versions) {
            const version = subitems.versions.find(({ Version }) => Version.VersionInfo.Content._href === location.Location.Content._href);

            mappedSubitems.version = version.Version;
        }

        return mappedSubitems;
    });
};

export const findLocationsByParentLocationId = (
    {
        token,
        siteaccess,
        accessToken,
        parentLocationId,
        limit = QUERY_LIMIT,
        offset = 0,
        sortClause = 'DatePublished',
        sortOrder = 'ascending',
        gridView = false,
        instanceUrl = DEFAULT_INSTANCE_URL,
    },
    callback,
) => {
    let url = `${instanceUrl}${ENDPOINT_LOCATION}/${parentLocationId}`;
    if (gridView) {
        url += '/gridview';
    }

    const request = new Request(`${url}?limit=${limit}&offset=${offset}&sortClause=${sortClause}&sortOrder=${sortOrder}`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const { bookmarked, location, permissions, subitems, version } = response.LocationData;
            const subitemsData = mapSubitems(subitems);
            const locationData = {
                location: location ? location.Location : null,
                version: version ? version.Version : null,
                totalCount: subitems.totalCount,
                subitems: subitemsData,
                bookmarked,
                permissions,
                parentLocationId,
            };

            callback(locationData);
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const loadAccordionData = async (
    {
        token,
        siteaccess,
        accessToken,
        parentLocationId,
        limit = QUERY_LIMIT,
        sortClause = 'DatePublished',
        sortOrder = 'ascending',
        gridView = false,
        rootLocationId = 1,
        instanceUrl = DEFAULT_INSTANCE_URL,
    },
    callback,
) => {
    let url = `${instanceUrl}${ENDPOINT_ACCORDION}/${parentLocationId}`;
    if (gridView) {
        url += '/gridview';
    }

    const request = new Request(`${url}?limit=${limit}&sortClause=${sortClause}&sortOrder=${sortOrder}&rootLocationId=${rootLocationId}`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const data = response.AccordionData;
            const mappedItems = data.breadcrumb.map((item) => {
                const location = item.Location;
                const itemData = data.columns[location.id];
                const mappedItem = {
                    location,
                    totalCount: itemData ? itemData.subitems.totalCount : undefined,
                    subitems: itemData ? mapSubitems(itemData.subitems) : [],
                    parentLocationId: location.id,
                    collapsed: !data.columns[location.id],
                };

                return mappedItem;
            });

            const rootLocationData = data.columns[1];
            const lastLocationData = data.columns[parentLocationId];

            if (rootLocationData) {
                mappedItems.unshift({
                    totalCount: rootLocationData ? rootLocationData.subitems.totalCount : undefined,
                    subitems: rootLocationData ? mapSubitems(rootLocationData.subitems) : [],
                    parentLocationId: 1,
                    collapsed: false,
                });
            }

            mappedItems.push({
                location: lastLocationData.location.Location,
                version: lastLocationData.version.Version,
                totalCount: lastLocationData ? lastLocationData.subitems.totalCount : undefined,
                subitems: lastLocationData ? mapSubitems(lastLocationData.subitems) : [],
                bookmarked: lastLocationData.bookmarked,
                permissions: lastLocationData.permissions,
                parentLocationId,
            });

            callback(mappedItems);
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const findLocationsBySearchQuery = (
    {
        token,
        siteaccess,
        accessToken,
        query,
        aggregations,
        filters,
        limit = QUERY_LIMIT,
        offset = 0,
        languageCode = null,
        instanceUrl = DEFAULT_INSTANCE_URL,
    },
    callback,
) => {
    const useAlwaysAvailable = true;
    const body = JSON.stringify({
        ViewInput: {
            identifier: `udw-locations-by-search-query-${query.FullTextCriterion}`,
            public: false,
            languageCode,
            useAlwaysAvailable,
            LocationQuery: {
                FacetBuilders: {},
                SortClauses: {},
                Query: query,
                Aggregations: aggregations,
                Filters: filters,
                limit,
                offset,
            },
        },
    });
    const request = new Request(`${instanceUrl}${ENDPOINT_CREATE_VIEW}`, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: HEADERS_CREATE_VIEW,
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const { count, aggregations: searchAggregations, searchHits } = response.View.Result;
            const items = searchHits.searchHit.map((searchHit) => searchHit.value.Location);

            callback({
                items,
                aggregations: searchAggregations,
                count,
            });
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const findLocationsById = (
    { token, siteaccess, accessToken, id, limit = QUERY_LIMIT, offset = 0, instanceUrl = DEFAULT_INSTANCE_URL },
    callback,
) => {
    const body = JSON.stringify({
        ViewInput: {
            identifier: `udw-locations-by-id-${id}`,
            public: false,
            LocationQuery: {
                FacetBuilders: {},
                SortClauses: { SectionIdentifier: 'ascending' },
                Filter: { LocationIdCriterion: id },
                limit,
                offset,
            },
        },
    });

    const request = new Request(`${instanceUrl}${ENDPOINT_CREATE_VIEW}`, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: HEADERS_CREATE_VIEW,
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const items = response.View.Result.searchHits.searchHit.map((searchHit) => searchHit.value.Location);

            callback(items);
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const findContentInfo = (
    { token, siteaccess, accessToken, contentId, limit = QUERY_LIMIT, offset = 0, instanceUrl = DEFAULT_INSTANCE_URL },
    callback,
) => {
    const body = JSON.stringify({
        ViewInput: {
            identifier: `udw-load-content-info-${contentId}`,
            public: false,
            ContentQuery: {
                FacetBuilders: {},
                SortClauses: {},
                Filter: { ContentIdCriterion: `${contentId}` },
                limit,
                offset,
            },
        },
    });
    const request = new Request(`${instanceUrl}${ENDPOINT_CREATE_VIEW}`, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: HEADERS_CREATE_VIEW,
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const items = response.View.Result.searchHits.searchHit.map((searchHit) => searchHit.value.Content);

            callback(items);
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const loadBookmarks = ({ token, siteaccess, accessToken, limit, offset, instanceUrl = DEFAULT_INSTANCE_URL }, callback) => {
    const request = new Request(`${instanceUrl}${ENDPOINT_BOOKMARK}?limit=${limit}&offset=${offset}`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const { count } = response.BookmarkList;
            const items = response.BookmarkList.items.map((item) => item.Location);

            callback({ count, items });
        })
        .catch(showErrorNotificationAbortWrapper);
};

const toggleBookmark = ({ siteaccess, token, accessToken, locationId, instanceUrl = DEFAULT_INSTANCE_URL }, callback, method) => {
    const request = new Request(`${instanceUrl}${ENDPOINT_BOOKMARK}/${locationId}`, {
        method,
        headers: getRequestHeaders({ token, siteaccess, accessToken }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request).then(handleRequestResponseStatus).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const addBookmark = (options, callback) => {
    toggleBookmark(options, callback, 'POST');
};

export const removeBookmark = (options, callback) => {
    toggleBookmark(options, callback, 'DELETE');
};

export const loadContentTypes = ({ token, siteaccess, accessToken, instanceUrl = DEFAULT_INSTANCE_URL }, callback) => {
    const request = new Request(`${instanceUrl}/api/ibexa/v2/content/types`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const createDraft = ({ token, siteaccess, accessToken, contentId, instanceUrl = DEFAULT_INSTANCE_URL }, callback) => {
    const request = new Request(`${instanceUrl}/api/ibexa/v2/content/objects/${contentId}/currentversion`, {
        method: 'COPY',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.VersionUpdate+json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const loadContentInfo = (
    { token, siteaccess, accessToken, contentId, limit = QUERY_LIMIT, offset = 0, signal, instanceUrl = DEFAULT_INSTANCE_URL },
    callback,
) => {
    const body = JSON.stringify({
        ViewInput: {
            identifier: `udw-load-content-info-${contentId}`,
            public: false,
            ContentQuery: {
                FacetBuilders: {},
                SortClauses: {},
                Filter: { ContentIdCriterion: `${contentId}` },
                limit,
                offset,
            },
        },
    });
    const request = new Request(`${instanceUrl}${ENDPOINT_CREATE_VIEW}`, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: HEADERS_CREATE_VIEW,
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request, { signal })
        .then(handleRequestResponse)
        .then((response) => {
            const items = response.View.Result.searchHits.searchHit.map((searchHit) => searchHit.value.Content);

            callback(items);
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const loadLocationsWithPermissions = (
    { token, siteaccess, accessToken, locationIds, signal, instanceUrl = DEFAULT_INSTANCE_URL },
    callback,
) => {
    const request = new Request(`${instanceUrl}${ENDPOINT_LOCATION_LIST}?locationIds=${locationIds}`, {
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.VersionUpdate+json',
            },
        }),
        method: 'GET',
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request, { signal }).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const fetchAdminConfig = async ({ token, siteaccess, accessToken, instanceUrl = DEFAULT_INSTANCE_URL }) => {
    const request = new Request(`${instanceUrl}/api/ibexa/v2/application-config`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    const adminUiData = await fetch(request);
    const adminUiConfig = await adminUiData.json();

    return adminUiConfig.ApplicationConfig;
}

export const findSuggestions = ({ siteaccess, token }, callback) => {
    const body = JSON.stringify({
        ViewInput: {
            identifier: 'view_with_aggregation',
            ContentQuery: {
                limit: '2',
                offset: '0',
                Aggregations: [
                    {
                        ContentTypeTermAggregation: {
                            name: 'content_type',
                        },
                    },
                ],
            },
        },
    });

    const request = new Request(ENDPOINT_CREATE_VIEW, {
        method: 'POST',
        headers: { ...HEADERS_CREATE_VIEW, 'X-Siteaccess': siteaccess, 'X-CSRF-Token': token },
        body,
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    fetch(request).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};
