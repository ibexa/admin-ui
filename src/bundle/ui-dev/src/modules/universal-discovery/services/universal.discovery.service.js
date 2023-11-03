import { showErrorNotification } from '../../common/services/notification.service';
import { handleRequestResponse, handleRequestResponseStatus } from '../../common/helpers/request.helper.js';
import Routing from '../../../../../../../../../friendsofsymfony/jsrouting-bundle/Resources/public/js/router';

const HEADERS_CREATE_VIEW = {
    Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
    'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
};
const ENDPOINT_CREATE_VIEW = '/api/ibexa/v2/views';
const ENDPOINT_BOOKMARK = '/api/ibexa/v2/bookmark';

export const QUERY_LIMIT = 50;

let RoutingInstance = window.Routing;

const getRoute = async (routeName, params) => {
    if (RoutingInstance) {
        return RoutingInstance.generate(routeName, params);
    }

    const fetchedRoutingData = await fetchRoutingData();

    RoutingInstance = Routing;
    RoutingInstance.setRoutingData(fetchedRoutingData);

    return RoutingInstance.generate(routeName, params);
};

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

export const findLocationsByParentLocationId = async (
    { token, parentLocationId, limit = QUERY_LIMIT, offset = 0, sortClause = 'DatePublished', sortOrder = 'ascending', gridView = false },
    callback,
) => {
    const routeName = gridView ? 'ibexa.udw.location.gridview.data' : 'ibexa.udw.location.data';
    const url = await getRoute(routeName, {
        locationId: parentLocationId,
    });
    const request = new Request(`${url}?limit=${limit}&offset=${offset}&sortClause=${sortClause}&sortOrder=${sortOrder}`, {
        method: 'GET',
        headers: { 'X-CSRF-Token': token },
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const { bookmarked, location, permissions, subitems, version } = response;
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
        parentLocationId,
        limit = QUERY_LIMIT,
        sortClause = 'DatePublished',
        sortOrder = 'ascending',
        gridView = false,
        rootLocationId = 1,
    },
    callback,
) => {
    const url = await getRoute(routeName, {
        locationId: parentLocationId,
    });
    const request = new Request(`${url}?limit=${limit}&sortClause=${sortClause}&sortOrder=${sortOrder}&rootLocationId=${rootLocationId}`, {
        method: 'GET',
        headers: { 'X-CSRF-Token': token },
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const mappedItems = response.breadcrumb.map((item) => {
                const location = item.Location;
                const itemData = response.columns[location.id];
                const mappedItem = {
                    location,
                    totalCount: itemData ? itemData.subitems.totalCount : undefined,
                    subitems: itemData ? mapSubitems(itemData.subitems) : [],
                    parentLocationId: location.id,
                    collapsed: !response.columns[location.id],
                };

                return mappedItem;
            });

            const rootLocationData = response.columns[1];
            const lastLocationData = response.columns[parentLocationId];

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
    { token, siteaccess, query, limit = QUERY_LIMIT, offset = 0, languageCode = null },
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
                limit,
                offset,
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

    fetch(request)
        .then(handleRequestResponse)
        .then((response) => {
            const { count, searchHits } = response.View.Result;
            const items = searchHits.searchHit.map((searchHit) => searchHit.value.Location);

            callback({
                items,
                count,
            });
        })
        .catch(showErrorNotificationAbortWrapper);
};

export const findLocationsById = ({ token, siteaccess, id, limit = QUERY_LIMIT, offset = 0 }, callback) => {
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
    const request = new Request(ENDPOINT_CREATE_VIEW, {
        method: 'POST',
        headers: { ...HEADERS_CREATE_VIEW, 'X-Siteaccess': siteaccess, 'X-CSRF-Token': token },
        body,
        mode: 'same-origin',
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

export const findContentInfo = ({ token, siteaccess, contentId, limit = QUERY_LIMIT, offset = 0 }, callback) => {
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
    const request = new Request(ENDPOINT_CREATE_VIEW, {
        method: 'POST',
        headers: { ...HEADERS_CREATE_VIEW, 'X-Siteaccess': siteaccess, 'X-CSRF-Token': token },
        body,
        mode: 'same-origin',
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

export const loadBookmarks = ({ token, siteaccess, limit, offset }, callback) => {
    const request = new Request(`${ENDPOINT_BOOKMARK}?limit=${limit}&offset=${offset}`, {
        method: 'GET',
        headers: {
            'X-Siteaccess': siteaccess,
            'X-CSRF-Token': token,
            Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
        },
        mode: 'same-origin',
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

const toggleBookmark = ({ siteaccess, token, locationId }, callback, method) => {
    const request = new Request(`${ENDPOINT_BOOKMARK}/${locationId}`, {
        method,
        headers: {
            'X-Siteaccess': siteaccess,
            'X-CSRF-Token': token,
        },
        mode: 'same-origin',
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

export const loadContentTypes = ({ token, siteaccess }, callback) => {
    const request = new Request('/api/ibexa/v2/content/types', {
        method: 'GET',
        headers: {
            Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
            'X-Siteaccess': siteaccess,
            'X-CSRF-Token': token,
        },
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    fetch(request).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const createDraft = ({ token, siteaccess, contentId }, callback) => {
    const request = new Request(`/api/ibexa/v2/content/objects/${contentId}/currentversion`, {
        method: 'COPY',
        headers: {
            Accept: 'application/vnd.ibexa.api.VersionUpdate+json',
            'X-Siteaccess': siteaccess,
            'X-CSRF-Token': token,
        },
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    fetch(request).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const loadContentInfo = ({ token, siteaccess, contentId, limit = QUERY_LIMIT, offset = 0, signal }, callback) => {
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
    const request = new Request(ENDPOINT_CREATE_VIEW, {
        method: 'POST',
        headers: { ...HEADERS_CREATE_VIEW, 'X-Siteaccess': siteaccess, 'X-CSRF-Token': token },
        body,
        mode: 'same-origin',
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

export const loadLocationsWithPermissions = async ({ locationIds, signal }, callback) => {
    const url = await getRoute('ibexa.udw.locations.data');
    const request = new Request(`${url}?locationIds=${locationIds}`, {
        method: 'GET',
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    fetch(request, { signal }).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

//Dummy request
function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}
export const fetchAdminConfig = async ({ token, siteaccess }) => {
    const request = new Request('/api/ibexa/v2/content/types', {
        method: 'GET',
        headers: {
            Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
            'X-Siteaccess': siteaccess,
            'X-CSRF-Token': token,
        },
        mode: 'same-origin',
        credentials: 'same-origin',
    });

    const resposne = await fetch(request);
    const jsonResponse = {
        userId: 14,
        backOfficeLanguage: 'pl_PL',
        languages: {
            mappings: {
                'eng-GB': {
                    name: 'English (United Kingdom)',
                    id: 2,
                    languageCode: 'eng-GB',
                    enabled: true,
                },
            },
            priority: ['eng-GB'],
        },
        section: {
            standard: 'Standard',
            users: 'Users',
            media: 'Media',
            form: 'Form',
            site_skeleton: 'Site skeleton',
            taxonomy: 'Taxonomy',
            product_taxonomy: 'Products Taxonomy',
            corporate_account: 'Corporate Account',
        },
        contentTypes: {
            Content: [
                {
                    id: 2,
                    identifier: 'article',
                    name: 'Article',
                    isContainer: true,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#article',
                    href: '/api/ibexa/v2/content/types/2',
                },
                {
                    id: 1,
                    identifier: 'folder',
                    name: 'Folder',
                    isContainer: true,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#folder',
                    href: '/api/ibexa/v2/content/types/1',
                },
                {
                    id: 43,
                    identifier: 'form',
                    name: 'Form',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#form',
                    href: '/api/ibexa/v2/content/types/43',
                },
                {
                    id: 42,
                    identifier: 'landing_page',
                    name: 'Landing page',
                    isContainer: true,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#landing_page',
                    href: '/api/ibexa/v2/content/types/42',
                },
                {
                    id: 45,
                    identifier: 'product_category_tag',
                    name: 'Product category',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/45',
                },
                {
                    id: 44,
                    identifier: 'tag',
                    name: 'Tag',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/44',
                },
            ],
            Users: [
                {
                    id: 46,
                    identifier: 'customer',
                    name: 'Customer',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/46',
                },
                {
                    id: 4,
                    identifier: 'user',
                    name: 'User',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#user',
                    href: '/api/ibexa/v2/content/types/4',
                },
                {
                    id: 3,
                    identifier: 'user_group',
                    name: 'User group',
                    isContainer: true,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#user_group',
                    href: '/api/ibexa/v2/content/types/3',
                },
            ],
            Media: [
                {
                    id: 12,
                    identifier: 'file',
                    name: 'File',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/12',
                },
                {
                    id: 5,
                    identifier: 'image',
                    name: 'Image',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaicons/img/all-icons.svg#image',
                    href: '/api/ibexa/v2/content/types/5',
                },
            ],
            'Customer Portal': [
                {
                    id: 52,
                    identifier: 'customer_portal',
                    name: 'Customer Portal',
                    isContainer: true,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/52',
                },
                {
                    id: 51,
                    identifier: 'customer_portal_page',
                    name: 'Customer Portal Page',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/51',
                },
            ],
            product: [],
            corporate_account: [
                {
                    id: 48,
                    identifier: 'company',
                    name: 'Company',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/48',
                    isHidden: true,
                },
                {
                    id: 50,
                    identifier: 'corporate_account_application',
                    name: 'Corporate Account Application',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/50',
                    isHidden: true,
                },
                {
                    id: 49,
                    identifier: 'shipping_address',
                    name: 'Shipping address',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/49',
                    isHidden: true,
                },
                {
                    id: 47,
                    identifier: 'member',
                    name: 'Member',
                    isContainer: false,
                    thumbnail: '/bundles/ibexaadminui/img/ibexa-icons.svg#file',
                    href: '/api/ibexa/v2/content/types/47',
                    isHidden: true,
                },
            ],
        },
        contentTree: {
            loadMoreLimit: 30,
            childrenLoadMaxLimit: 200,
            treeMaxDepth: 10,
            allowedContentTypes: [],
            ignoredContentTypes: [],
            treeRootLocationId: 2,
            contextualTreeRootLocationIds: [2, 5, 43, 48, 55, 56, 60],
        },
        userContentTypes: ['user', 'member'],
        timezone: 'UTC',
        dateFormat: {
            fullDateTimeFormat: 'LLLL dd, yyyy HH:mm',
            shortDateTimeFormat: 'dd/MM/yyyy HH:mm',
        },
        iconPaths: {
            iconSets: {
                streamlineicons: '/bundles/ibexaicons/img/all-icons.svg',
            },
            defaultIconSet: 'streamlineicons',
        },
    };

    return jsonResponse;
};

export const fetchRoutingData = async () => {
    const response = await fetch('/admin/js/routing');
    const jsonResponse = await response.json();

    return jsonResponse;
};
