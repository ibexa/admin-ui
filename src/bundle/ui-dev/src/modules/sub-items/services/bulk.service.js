import { getAdminUiConfig, getRestInfo, getTranslator } from '@ibexa-admin-ui-helpers/context.helper.js';
import { getRequestHeaders, getRequestMode, getJsonFromResponse } from '@ibexa-admin-ui-helpers/request.helper';
import { showErrorNotification } from '@ibexa-admin-ui-helpers/notification.helper';
import {
    TRASH_FAKE_LOCATION,
    USER_ENDPOINT,
    LOCATION_ENDPOINT,
    CONTENT_OBJECTS_ENDPOINT,
    ENDPOINT_BULK,
    HEADERS_BULK,
} from './endpoints.js';

export const bulkMoveLocations = (items, newLocationHref, callback) => {
    const requestBodyOperations = {};

    items.forEach(({ id, pathString }) => {
        requestBodyOperations[id] = getBulkMoveRequestOperation(pathString, newLocationHref);
    });

    makeBulkRequest(requestBodyOperations, processBulkResponse.bind(null, items, callback));
};

export const bulkAddLocations = (items, newLocationHref, callback) => {
    const requestBodyOperations = {};

    items.forEach(({ id, contentInfo }) => {
        requestBodyOperations[id] = getBulkAddLocationRequestOperation(contentInfo.ContentInfo.id, newLocationHref);
    });

    makeBulkRequest(requestBodyOperations, processBulkResponse.bind(null, items, callback));
};

export const bulkHideLocations = (items, callback) => {
    const requestBodyOperations = {};

    items.forEach(({ id, pathString }) => {
        requestBodyOperations[id] = getBulkVisibilityRequestOperation(pathString, true);
    });

    makeBulkRequest(requestBodyOperations, processBulkResponse.bind(null, items, callback));
};

export const bulkUnhideLocations = (items, callback) => {
    const requestBodyOperations = {};

    items.forEach(({ id, pathString }) => {
        requestBodyOperations[id] = getBulkVisibilityRequestOperation(pathString, false);
    });

    makeBulkRequest(requestBodyOperations, processBulkResponse.bind(null, items, callback));
};

export const bulkDeleteItems = (items, callback) => {
    const requestBodyOperations = {};
    const adminUiConfig = getAdminUiConfig();

    items.forEach((item) => {
        const { id: locationId, pathString, contentType, contentInfo } = item;
        const contentTypeIdentifier = contentType.ContentType.identifier;
        const isUserContentItem = adminUiConfig.userContentTypes.includes(contentTypeIdentifier);
        const contentId = contentInfo.ContentInfo.id;

        if (isUserContentItem) {
            requestBodyOperations[locationId] = getBulkDeleteUserRequestOperation(contentId);
        } else {
            requestBodyOperations[locationId] = getBulkMoveRequestOperation(pathString, TRASH_FAKE_LOCATION);
        }
    });

    makeBulkRequest(requestBodyOperations, processBulkResponse.bind(null, items, callback));
};

const getBulkDeleteUserRequestOperation = (contentId) => ({
    uri: `${USER_ENDPOINT}/${contentId}`,
    method: 'DELETE',
});

const getBulkMoveRequestOperation = (pathString, destination) => ({
    uri: `${LOCATION_ENDPOINT}${pathString.slice(0, -1)}`,
    method: 'MOVE',
    headers: {
        Destination: destination,
    },
});

const getBulkAddLocationRequestOperation = (contentId, destination) => ({
    uri: `${CONTENT_OBJECTS_ENDPOINT}/${contentId}/locations`,
    content: JSON.stringify({
        LocationCreate: {
            ParentLocation: {
                _href: destination,
            },
            sortField: 'PATH',
            sortOrder: 'ASC',
        },
    }),
    headers: {
        'Content-Type': 'application/vnd.ibexa.api.LocationCreate+json',
    },
    method: 'POST',
});

const getBulkVisibilityRequestOperation = (pathString, isHidden) => ({
    uri: `${LOCATION_ENDPOINT}${pathString.slice(0, -1)}`,
    content: JSON.stringify({
        LocationUpdate: {
            hidden: isHidden,
            sortField: 'PATH',
            sortOrder: 'ASC',
        },
    }),
    headers: {
        'Content-Type': 'application/vnd.ibexa.api.LocationUpdate+json',
    },
    method: 'PATCH',
});

const processBulkResponse = (items, callback, response) => {
    const { operations } = response.BulkOperationResponse;
    const itemsMatches = Object.entries(operations).reduce(
        (output, [locationId, { statusCode }]) => {
            const respectiveItem = items.find((item) => item.id === parseInt(locationId, 10));
            const isSuccess = 200 <= statusCode && statusCode <= 299;

            if (isSuccess) {
                output.success.push(respectiveItem);
            } else {
                output.fail.push(respectiveItem);
            }

            return output;
        },
        { success: [], fail: [] },
    );

    callback(itemsMatches.success, itemsMatches.fail);
};

const makeBulkRequest = (requestBodyOperations, callback) => {
    const Translator = getTranslator();
    const { token, siteaccess, accessToken, instanceUrl } = getRestInfo();

    const request = new Request(ENDPOINT_BULK, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: HEADERS_BULK,
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
        body: JSON.stringify({
            bulkOperations: {
                operations: requestBodyOperations,
            },
        }),
    });

    fetch(request)
        .then(getJsonFromResponse)
        .then(callback)
        .catch(() => {
            const message = Translator.trans(
                /*@Desc("An unexpected error occurred while processing the Content item(s). Please try again later.")*/
                'bulk_request.error.message',
                {},
                'ibexa_sub_items',
            );

            showErrorNotification(message);
        });
};
