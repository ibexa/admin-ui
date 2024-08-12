import { getTranslator } from './context.helper';

const handleRequest = (response) => {
    if (!response.ok) {
        const Translator = getTranslator();
        const defaultErrorMsg = Translator.trans(
            /*@Desc("Something went wrong. Try to refresh the page or contact your administrator.")*/ 'error.request.default_msg',
        );

        throw Error(response.statusText || defaultErrorMsg);
    }

    return response;
};

const getJsonFromResponse = (response) => {
    return handleRequest(response).json();
};

const getTextFromResponse = (response) => {
    return handleRequest(response).text();
};

const getStatusFromResponse = (response) => {
    return handleRequest(response).status;
};

const getRequestMode = ({ instanceUrl }) => {
    return window.location.origin === instanceUrl ? 'same-origin' : 'cors';
};

const getRequestHeaders = ({ token, siteaccess, accessToken, extraHeaders }) => {
    if (accessToken) {
        return {
            Authorization: `Bearer ${accessToken}`,
            ...(siteaccess && { 'X-Siteaccess': siteaccess }),
            ...extraHeaders,
        };
    }

    return {
        ...(token && { 'X-CSRF-Token': token }),
        ...(siteaccess && { 'X-Siteaccess': siteaccess }),
        ...extraHeaders,
    };
};

export { getJsonFromResponse, getTextFromResponse, getStatusFromResponse, getRequestMode, getRequestHeaders };
