import { getRestInfo, getTranslator } from '@ibexa-admin-ui-helpers/context.helper.js';
import { getRequestHeaders, getRequestMode, getJsonFromResponse } from '@ibexa-admin-ui-helpers/request.helper';
import { showErrorNotification } from '@ibexa-admin-ui-helpers/notification.helper';
import { LOCATION_ENDPOINT, ENDPOINT_LOCATION_SUBITEMS } from './endpoints.js';

/**
 * Loads location's children
 *
 * @function loadLocation
 * @param {Object} queryConfig - contains:
 * @param {Number} queryConfig.locationId
 * @param {Number} queryConfig.offset
 * @param {Number} queryConfig.limit
 * @param {Object} queryConfig.sortClause
 * @param {Object} queryConfig.sortOrder
 * @param {Function} callback
 */
export const loadLocation = ({ locationId = 2, offset = 0, limit = 10, sortClause, sortOrder }, callback) => {
    const Translator = getTranslator();
    const { token, siteaccess, accessToken, instanceUrl } = getRestInfo();
    const loadSubItemsUrl = `${ENDPOINT_LOCATION_SUBITEMS}/${locationId}/${limit}/${offset}?sortClause=${sortClause}&sortOrder=${sortOrder}`;
    const request = new Request(loadSubItemsUrl, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(getJsonFromResponse)
        .then(callback)
        .catch(() =>
            showErrorNotification(
                Translator.trans(/* @Desc("Cannot load location") */ 'load_location.request.error', {}, 'ibexa_sub_items'),
            ),
        );
};

/**
 * Updates location priority
 *
 * @function updateLocationPriority
 * @param {Object} params params hash containing: priority, location properties
 * @param {Function} callback
 */
export const updateLocationPriority = ({ priority, pathString }, callback) => {
    const Translator = getTranslator();
    const { token, siteaccess, accessToken, instanceUrl } = getRestInfo();
    const locationHref = `${LOCATION_ENDPOINT}${pathString.slice(0, -1)}`;

    const request = new Request(locationHref, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.Location+json',
                'Content-Type': 'application/vnd.ibexa.api.LocationUpdate+json',
                'X-HTTP-Method-Override': 'PATCH',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
        body: JSON.stringify({
            LocationUpdate: {
                priority: priority,
            },
        }),
    });

    fetch(request)
        .then(getJsonFromResponse)
        .then(callback)
        .catch(() =>
            showErrorNotification(
                Translator.trans(
                    /* @Desc("An error occurred while updating location priority") */ 'update_location_priority.request.error',
                    {},
                    'ibexa_sub_items',
                ),
            ),
        );
};
