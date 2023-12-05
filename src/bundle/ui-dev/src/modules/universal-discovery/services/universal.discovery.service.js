import { getRequestHeaders, getRequestMode, getRequestCredencials } from '../../common/services/common.service.js';
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
            }
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            }
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            extraHeaders: HEADERS_CREATE_VIEW
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            extraHeaders: HEADERS_CREATE_VIEW
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            extraHeaders: HEADERS_CREATE_VIEW
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
                Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json'
            }
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
        credentials: getRequestCredencials({ instanceUrl }),
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
            }
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            }
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            extraHeaders: HEADERS_CREATE_VIEW
        }),
        body,
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
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
            }
        }),
        method: 'GET',
        mode: getRequestMode({ instanceUrl }),
        credentials: getRequestCredencials({ instanceUrl }),
    });

    fetch(request, { signal }).then(handleRequestResponse).then(callback).catch(showErrorNotificationAbortWrapper);
};

export const fetchAdminConfig = async ({ token, siteaccess, accessToken, instanceUrl = DEFAULT_INSTANCE_URL }) => {
    // const request = new Request(`${instanceUrl}/api/ibexa/v2/application-config`, {
    //     method: 'GET',
    //     headers: {
    //         Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
    //         ...getAuthenticationHeaders({ token, siteaccess, accessToken }),
    //     },
    //      mode: getRequestMode({ instanceUrl }),
    //     credentials: getRequestCredencials({ instanceUrl }),
    // });

    // const resposne = await fetch(request);
    // const jsonResponse = await resposne.json();
    // const adminUiConfig = jsonResponse.ApplicationConfig;

    // adminUiConfig['user_Id'] =
    // console.log(jsonResponse.ApplicationConfig);

    // return jsonResponse.ApplicationConfig;

    // console.log(jsonResponse.ApplicationConfig)
    return {
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
        sections: {
            standard: 'Standard',
            users: 'Users',
            media: 'Media',
            form: 'Form',
            site_skeleton: 'Site skeleton',
            taxonomy: 'Taxonomy',
            product_taxonomy: 'Products Taxonomy',
            corporate_account: 'Corporate Account',
        },
        userContentTypes: ['user', 'member'],
        timezone: 'UTC',
        dateFormat: {
            fullDateTime: 'LLLL dd, yyyy HH:mm',
            fullDate: 'LLLL dd, yyyy',
            fullTime: 'HH:mm',
            shortDateTime: 'dd/MM/yyyy HH:mm',
            shortDate: 'dd/MM/yyyy',
            shortTime: 'HH:mm',
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

export const fetchRoutingData = async (instanceUrl = DEFAULT_INSTANCE_URL) => {
    return {
        base_url: '/admin',
        routes: {
            'ibexa.content.translation.view': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['text', '/translation'],
                    ['variable', '/', '[^/]++', 'layout', true],
                    ['variable', '/', '[^/]++', 'viewType', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/view/content'],
                ],
                defaults: {
                    viewType: 'full',
                    locationId: null,
                    layout: true,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content.view': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'layout', true],
                    ['variable', '/', '[^/]++', 'viewType', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/view/content'],
                ],
                defaults: {
                    viewType: 'full',
                    locationId: null,
                    layout: true,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content_type.copy': {
                tokens: [
                    ['text', '/copy'],
                    ['variable', '/', '[^/]++', 'contentTypeId', true],
                    ['text', '/contenttype'],
                    ['variable', '/', '\\d+', 'contentTypeGroupId', true],
                    ['text', '/contenttypegroup'],
                ],
                defaults: [],
                requirements: {
                    contentTypeGroupId: '\\d+',
                },
                hosttokens: [],
                methods: ['GET', 'POST'],
                schemes: [],
            },
            'ibexa.content_type.field_definition_form': {
                tokens: [
                    ['variable', '/', '[^/]++', 'fromLanguageCode', true],
                    ['variable', '/', '[^/]++', 'toLanguageCode', true],
                    ['variable', '/', '[^/]++', 'fieldDefinitionIdentifier', true],
                    ['text', '/field_definition_form'],
                    ['variable', '/', '[^/]++', 'contentTypeId', true],
                    ['text', '/contenttype'],
                    ['variable', '/', '\\d+', 'contentTypeGroupId', true],
                    ['text', '/contenttypegroup'],
                ],
                defaults: {
                    toLanguageCode: null,
                    fromLanguageCode: null,
                },
                requirements: {
                    contentTypeGroupId: '\\d+',
                },
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.version_draft.has_no_conflict': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/version-draft/has-no-conflict'],
                ],
                defaults: {
                    locationId: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content.create.proxy': {
                tokens: [
                    ['variable', '/', '[^/]++', 'parentLocationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentTypeIdentifier', true],
                    ['text', '/content/create/proxy'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content.preview': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'versionNo', true],
                    ['text', '/preview'],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/content'],
                ],
                defaults: {
                    languageCode: null,
                    locationId: null,
                },
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.content.check_edit_permission': {
                tokens: [
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['text', '/check-edit-permission'],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/content'],
                ],
                defaults: {
                    languageCode: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content.on_the_fly.create': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentTypeIdentifier', true],
                    ['text', '/content/create/on-the-fly'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET', 'POST'],
                schemes: [],
            },
            'ibexa.content.on_the_fly.edit': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'versionNo', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/content/edit/on-the-fly'],
                ],
                defaults: {
                    locationId: null,
                },
                requirements: [],
                hosttokens: [],
                methods: ['GET', 'POST'],
                schemes: [],
            },
            'ibexa.content.on_the_fly.has_access': {
                tokens: [
                    ['text', '/has-access'],
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentTypeIdentifier', true],
                    ['text', '/content/create/on-the-fly'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.user.on_the_fly.create': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentTypeIdentifier', true],
                    ['text', '/user/create/on-the-fly'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET', 'POST'],
                schemes: [],
            },
            'ibexa.user.on_the_fly.edit': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'versionNo', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/user/edit/on-the-fly'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET', 'POST'],
                schemes: [],
            },
            'ibexa.user.on_the_fly.has_access': {
                tokens: [
                    ['text', '/has-access'],
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentTypeIdentifier', true],
                    ['text', '/user/create/on-the-fly'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.asset.upload_image': {
                tokens: [['text', '/asset/image']],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['POST'],
                schemes: [],
            },
            'ibexa.permission.limitation.language.content_create': {
                tokens: [
                    ['variable', '/', '\\d+', 'locationId', true],
                    ['text', '/permission/limitation/language/content-create'],
                ],
                defaults: [],
                requirements: {
                    locationId: '\\d+',
                },
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.permission.limitation.language.content_edit': {
                tokens: [
                    ['variable', '/', '\\d+', 'contentInfoId', true],
                    ['text', '/permission/limitation/language/content-edit'],
                ],
                defaults: [],
                requirements: {
                    contentInfoId: '\\d+',
                },
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.permission.limitation.language.content_read': {
                tokens: [
                    ['variable', '/', '\\d+', 'contentInfoId', true],
                    ['text', '/permission/limitation/language/content-read'],
                ],
                defaults: [],
                requirements: {
                    contentInfoId: '\\d+',
                },
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.rest.bulk_operation': {
                tokens: [['text', '/api/ibexa/v2/bulk']],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['POST'],
                schemes: [],
            },
            'ibexa.rest.location.tree.load_children': {
                tokens: [
                    ['variable', '/', '[^/]++', 'offset', true],
                    ['variable', '/', '[^/]++', 'limit', true],
                    ['variable', '/', '\\d+', 'parentLocationId', true],
                    ['text', '/api/ibexa/v2/location/tree/load-subitems'],
                ],
                defaults: {
                    limit: 10,
                    offset: 0,
                },
                requirements: {
                    parentLocationId: '\\d+',
                },
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.rest.location.tree.load_subtree': {
                tokens: [['text', '/api/ibexa/v2/location/tree/load-subtree']],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['POST'],
                schemes: [],
            },
            'ibexa.udw.location.data': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['text', '/api/ibexa/v2/module/universal-discovery/location'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.udw.location.gridview.data': {
                tokens: [
                    ['text', '/gridview'],
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['text', '/api/ibexa/v2/module/universal-discovery/location'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.udw.locations.data': {
                tokens: [['text', '/api/ibexa/v2/module/universal-discovery/locations']],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.udw.accordion.data': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['text', '/api/ibexa/v2/module/universal-discovery/accordion'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.udw.accordion.gridview.data': {
                tokens: [
                    ['text', '/gridview'],
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['text', '/api/ibexa/v2/module/universal-discovery/accordion'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.rest.application_config': {
                tokens: [['text', '/api/ibexa/v2/application-config/']],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.connector.dam.asset_view': {
                tokens: [
                    ['variable', '/', '[^/]++', 'transformation', true],
                    ['variable', '/', '[^/]++', 'assetSource', true],
                    ['variable', '/', '[^/]++', 'destinationContentId', true],
                    ['text', '/view/asset'],
                ],
                defaults: {
                    transformation: null,
                },
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.content.create_no_draft': {
                tokens: [
                    ['variable', '/', '[^/]++', 'parentLocationId', true],
                    ['variable', '/', '[^/]++', 'language', true],
                    ['variable', '/', '[^/]++', 'contentTypeIdentifier', true],
                    ['text', '/content/create/nodraft'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content.draft.edit': {
                tokens: [
                    ['variable', '/', '[^/]++', 'locationId', true],
                    ['variable', '/', '[^/]++', 'language', true],
                    ['variable', '/', '[^/]++', 'versionNo', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/content/edit/draft'],
                ],
                defaults: {
                    language: null,
                    locationId: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.content.draft.create': {
                tokens: [
                    ['variable', '/', '[^/]++', 'fromLanguage', true],
                    ['variable', '/', '[^/]++', 'fromVersionNo', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/content/create/draft'],
                ],
                defaults: {
                    contentId: null,
                    fromVersionNo: null,
                    fromLanguage: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.user.update': {
                tokens: [
                    ['variable', '/', '[^/]++', 'language', true],
                    ['variable', '/', '[^/]++', 'versionNo', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/user/update'],
                ],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.image_editor.update_image_asset': {
                tokens: [
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/image-editor/update'],
                ],
                defaults: {
                    languageCode: null,
                },
                requirements: [],
                hosttokens: [],
                methods: ['PUT'],
                schemes: [],
            },
            'ibexa.image_editor.create_from_image_asset': {
                tokens: [
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'fromContentId', true],
                    ['text', '/image-editor/create-from'],
                ],
                defaults: {
                    languageCode: null,
                },
                requirements: [],
                hosttokens: [],
                methods: ['POST'],
                schemes: [],
            },
            'ibexa.image_editor.get_base_64': {
                tokens: [
                    ['variable', '/', '[^/]++', 'languageCode', true],
                    ['variable', '/', '[^/]++', 'versionNo', true],
                    ['variable', '/', '[^/]++', 'fieldIdentifier', true],
                    ['variable', '/', '[^/]++', 'contentId', true],
                    ['text', '/image-editor/base64'],
                ],
                defaults: {
                    versionNo: null,
                    languageCode: null,
                },
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.search.suggestion': {
                tokens: [['text', '/suggestion']],
                defaults: [],
                requirements: [],
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
            'ibexa.user_settings.update': {
                tokens: [
                    ['variable', '/', '.+', 'identifier', true],
                    ['text', '/user/settings/update'],
                ],
                defaults: [],
                requirements: {
                    identifier: '.+',
                },
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.version.compare.split': {
                tokens: [
                    ['variable', '/', '[^/]++', 'versionBLanguageCode', true],
                    ['variable', '/', '[^/]++', 'versionNoB', true],
                    ['variable', '/', '[^/]++', 'versionALanguageCode', true],
                    ['variable', '/', '[^/]++', 'versionNoA', true],
                    ['variable', '/', '[^/]++', 'contentInfoId', true],
                    ['text', '/version/compare-split'],
                ],
                defaults: {
                    versionNoB: null,
                    versionBLanguageCode: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.version.compare.unified': {
                tokens: [
                    ['variable', '/', '[^/]++', 'versionBLanguageCode', true],
                    ['variable', '/', '[^/]++', 'versionNoB', true],
                    ['variable', '/', '[^/]++', 'versionALanguageCode', true],
                    ['variable', '/', '[^/]++', 'versionNoA', true],
                    ['variable', '/', '[^/]++', 'contentInfoId', true],
                    ['text', '/version/comparison-unified'],
                ],
                defaults: {
                    versionNoB: null,
                    versionBLanguageCode: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.version.side_by_side_comparison': {
                tokens: [
                    ['variable', '/', '[^/]++', 'versionNoB', true],
                    ['variable', '/', '[^/]++', 'versionNoA', true],
                    ['variable', '/', '[^/]++', 'contentInfoId', true],
                    ['text', '/version/side-by-side-comparison'],
                ],
                defaults: {
                    versionNoB: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.version.compare': {
                tokens: [
                    ['variable', '/', '[^/]++', 'versionNoB', true],
                    ['variable', '/', '[^/]++', 'versionNoA', true],
                    ['variable', '/', '[^/]++', 'contentInfoId', true],
                    ['text', '/version/comparison'],
                ],
                defaults: {
                    versionNoB: null,
                },
                requirements: [],
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.workflow.content_create.reviewer_suggest': {
                tokens: [
                    ['variable', '/', '\\d+', 'locationId', true],
                    ['text', '/location'],
                    ['variable', '/', '.+', 'languageCode', true],
                    ['text', '/language'],
                    ['variable', '/', '.+', 'contentTypeIdentifier', true],
                    ['text', '/reviewers-suggest/content-create/content-type'],
                    ['variable', '/', '.+', 'transitionName', true],
                    ['text', '/transition'],
                    ['variable', '/', '.+', 'workflowName', true],
                    ['text', '/workflow'],
                ],
                defaults: [],
                requirements: {
                    workflowName: '.+',
                    transitionName: '.+',
                    contentTypeIdentifier: '.+',
                    languageCode: '.+',
                    locationId: '\\d+',
                },
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            'ibexa.workflow.content_edit.reviewer_suggest': {
                tokens: [
                    ['variable', '/', '\\d+', 'locationId', true],
                    ['text', '/location'],
                    ['variable', '/', '\\d+', 'versionNo', true],
                    ['text', '/version'],
                    ['variable', '/', '\\d+', 'contentId', true],
                    ['text', '/reviewers-suggest/content-edit/content'],
                    ['variable', '/', '.+', 'transitionName', true],
                    ['text', '/transition'],
                    ['variable', '/', '.+', 'workflowName', true],
                    ['text', '/workflow'],
                ],
                defaults: [],
                requirements: {
                    workflowName: '.+',
                    transitionName: '.+',
                    contentId: '\\d+',
                    versionNo: '\\d+',
                    locationId: '\\d+',
                },
                hosttokens: [],
                methods: [],
                schemes: [],
            },
            bazinga_jstranslation_js: {
                tokens: [
                    ['variable', '.', 'js|json', '_format', true],
                    ['variable', '/', '[\\w]+', 'domain', true],
                    ['text', '/translations'],
                ],
                defaults: {
                    domain: 'messages',
                    _format: 'js',
                },
                requirements: {
                    _format: 'js|json',
                    domain: '[\\w]+',
                },
                hosttokens: [],
                methods: ['GET'],
                schemes: [],
            },
        },
        prefix: '',
        host: '127.0.0.1:8000',
        port: '8000',
        scheme: 'https',
        locale: 'en',
    };
    // const response = await fetch(`${instanceUrl}/admin/js/routing`);
    // const jsonResponse = await response.json();

    // return jsonResponse;
};
