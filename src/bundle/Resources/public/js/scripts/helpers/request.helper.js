import { getTranslator } from './context.helper';

const defaultGetErrorMessage = (error = {}) => error.errorMessage;

const extractXmlErrorMessage = (xmlText) => {
    const match = xmlText.match(/<errorMessage>([\s\S]*?)<\/errorMessage>/i);

    return match ? match[1].trim() : null;
};

const getErrorMessageObject = async (response) => {
    const contentType = response.headers.get('content-type') ?? '';
    const isJsonResponse = contentType.includes('json');

    if (isJsonResponse) {
        const jsonResponse = await response.json();

        return jsonResponse.ErrorMessage ?? jsonResponse;
    }

    const textResponse = await response.text();
    const errorMessage = extractXmlErrorMessage(textResponse) || textResponse.trim() || response.statusText;

    return { errorMessage };
};

const handleRequest = async (response, getErrorMessage = defaultGetErrorMessage) => {
    if (!response.ok) {
        const Translator = getTranslator();
        const responseErrorMessageObject = await getErrorMessageObject(response);
        const errorMessage = getErrorMessage(responseErrorMessageObject) || response.statusText;
        const defaultErrorMsg = Translator.trans(
            /* @Desc("Something went wrong. Try to refresh the page or contact your administrator.") */ 'error.request.default_msg',
        );

        throw Error(errorMessage || defaultErrorMsg);
    }

    return response;
};

const getJsonFromResponse = async (response, getErrorMessage) => {
    const parsedRequest = await handleRequest(response, getErrorMessage);

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

const getRequestHeaders = ({ token, siteaccess, accessToken, accept = null, extraHeaders }) => {
    if (accessToken) {
        return {
            Authorization: `Bearer ${accessToken}`,
            ...(siteaccess && { 'X-Siteaccess': siteaccess }),
            ...(accept && { Accept: accept }),
            ...extraHeaders,
        };
    }

    return {
        ...(token && { 'X-CSRF-Token': token }),
        ...(siteaccess && { 'X-Siteaccess': siteaccess }),
        ...(accept && { Accept: accept }),
        ...extraHeaders,
    };
};

export { getJsonFromResponse, getTextFromResponse, getStatusFromResponse, getRequestMode, getRequestHeaders };
