(function(global, doc, ibexa) {
    /**
     * Handles request error
     *
     * @function handleRequest
     * @param {Response} response
     * @returns {Error|Response}
     */
    const handleRequest = (response) => {
        if (!response.ok) {
            throw Error(response.statusText);
        }

        return response;
    };

    /**
     * Handles request JSON response
     *
     * @function getJsonFromResponse
     * @param {Response} response
     * @returns {Error|Promise}
     */
    const getJsonFromResponse = (response) => {
        return handleRequest(response).json();
    };

    /**
     * Handles request text response
     *
     * @function getTextFromResponse
     * @param {Response} response
     * @returns {Error|Promise}
     */
    const getTextFromResponse = (response) => {
        return handleRequest(response).text();
    };

    /**
     * Handles request response; returns status if response is OK
     *
     * @function getStatusFromResponse
     * @param {Response} response
     * @returns {Error|Promise}
     */
    const getStatusFromResponse = (response) => {
        return handleRequest(response).status;
    };

    ibexa.addConfig('helpers.request', {
        getJsonFromResponse,
        getTextFromResponse,
        getStatusFromResponse,
    });
})(window, window.document, window.ibexa);
