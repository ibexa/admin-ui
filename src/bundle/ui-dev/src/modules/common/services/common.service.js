export const ENDPOINT_VIEWS = '/api/ibexa/v2/views';
export const ENDPOINT_CONTENT_TYPES = '/api/ibexa/v2/content/types';
export const HEADERS_VIEWS = {
    Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
    'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
};

export const getRequestMode = ({ instanceUrl }) => {
    return window.location.origin === instanceUrl ? 'same-origin' : 'cors';
}

export const getRequestHeaders = ({ token, siteaccess, accessToken, extraHeaders }) => {
    if (accessToken) {
        return {
            Authorization: `Bearer ${accessToken}`,
            ...(siteaccess && { 'X-Siteaccess': siteaccess }),
            ...extraHeaders
        };
    }

    return {
        ...(token && { 'X-CSRF-Token': token }),
        ...(siteaccess && { 'X-Siteaccess': siteaccess }),
        ...extraHeaders
    };
};

export const handleRequestResponse = (response) => {
    if (!response.ok) {
        throw Error(response.statusText);
    }

    return response.json();
};
