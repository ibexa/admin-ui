import { getTranslator } from './context.helper';

const getErrorMessage = async (response) => {
    const responseErrorMessage = await response.json().then((respo) => {
        return respo.ErrorMessage?.errorMessage;
    });

    return responseErrorMessage;
};

const handleRequest = async (response) => {
    if (!response.ok) {
        const Translator = getTranslator();
        const responseErrorMessage = await getErrorMessage(response);
        const errorMessage = responseErrorMessage || response.statusText;
        const defaultErrorMsg = Translator.trans(
            /*@Desc("Something went wrong. Try to refresh the page or contact your administrator.")*/ 'error.request.default_msg',
        );

        throw Error(errorMessage || defaultErrorMsg);
    }

    return response;
};

const getJsonFromResponse = async (response) => {
    const parsedRequest = await handleRequest(response);

    return parsedRequest.json();
};

const getTextFromResponse = async (response) => {
    const parsedRequest = await handleRequest(response);

    return parsedRequest.text();
};

const getStatusFromResponse = async (response) => {
    const parsedRequest = await handleRequest(response);

    return parsedRequest.status;
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
