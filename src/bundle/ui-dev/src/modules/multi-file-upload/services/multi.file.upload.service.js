import { getTranslator, getRestInfo } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { getRequestHeaders, getRequestMode } from '../../../../../Resources/public/js/scripts/helpers/request.helper';

const handleOnReadyStateChange = (xhr, onSuccess, onError) => {
    if (xhr.readyState !== 4) {
        return;
    }

    if (xhr.status === 0 && xhr.statusText === '') {
        // request aborted
        return;
    }

    if (xhr.status >= 400 || !xhr.status) {
        onError(xhr);

        return;
    }

    onSuccess(JSON.parse(xhr.response));
};
const handleRequestResponse = (response) => {
    if (!response.ok) {
        throw Error(response.text());
    }

    return response;
};
const readFile = function (file, resolve, reject) {
    this.addEventListener('load', () => resolve({ fileReader: this, file }), false);
    this.addEventListener('error', () => reject(), false);
    this.readAsDataURL(file);
};
const findFileTypeMapping = (mappings, file) => mappings.find((item) => item.mimeTypes.find((type) => type === file.type));
const isMimeTypeAllowed = (mappings, file) => !!findFileTypeMapping(mappings, file);

const checkFileTypeAllowed = (file, locationMapping) => (!locationMapping ? true : isMimeTypeAllowed(locationMapping.mappings, file));

const detectContentTypeMapping = (file, parentInfo, config) => {
    const locationMapping = config.locationMappings.find((item) => item.contentTypeIdentifier === parentInfo.contentTypeIdentifier);
    const mappings = locationMapping ? locationMapping.mappings : config.defaultMappings;

    return findFileTypeMapping(mappings, file) || config.fallbackContentType;
};
const getContentTypeByIdentifier = (identifier) => {
    const { instanceUrl, token, siteaccess, accessToken } = getRestInfo();
    const request = new Request(`${instanceUrl}/api/ibexa/v2/content/types?identifier=${identifier}`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.ContentTypeInfoList+json',
            },
        }),
        credentials: 'same-origin',
        mode: getRequestMode({ instanceUrl }),
    });

    return fetch(request).then(handleRequestResponse);
};
const getFieldDefinitionByIdentifier = (contentTypeId, fieldIdentifier) => {
    const { instanceUrl, token, siteaccess, accessToken } = getRestInfo();
    const request = new Request(`${instanceUrl}/api/ibexa/v2/content/types/${contentTypeId}/fieldDefinition/${fieldIdentifier}`, {
        method: 'GET',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.FieldDefinition+json',
            },
        }),
        credentials: 'same-origin',
        mode: getRequestMode({ instanceUrl }),
    });

    return fetch(request).then(handleRequestResponse);
};
const prepareStruct = ({ parentInfo, config, languageCode }, data, contentErrorCallback) => {
    const Translator = getTranslator();
    let parentLocation = `/api/ibexa/v2/content/locations${parentInfo.locationPath}`;

    parentLocation = parentLocation.endsWith('/') ? parentLocation.slice(0, -1) : parentLocation;

    const mapping = detectContentTypeMapping(data.file, parentInfo, config.multiFileUpload);

    return getContentTypeByIdentifier(mapping.contentTypeIdentifier)
        .then((response) => response.json())
        .catch(() => {
            contentErrorCallback(
                Translator.trans(
                    /*@Desc("Cannot get content type by identifier")*/ 'cannot_get_content_type_identifier.message',
                    {},
                    'ibexa_multi_file_upload',
                ),
            );
        })
        .then((response) => {
            const fileValue = {
                fileName: data.file.name,
                data: data.fileReader.result.replace(/^.*;base64,/, ''),
            };

            const contentType = response.ContentTypeInfoList.ContentType[0];
            const { contentFieldIdentifier } = mapping;

            return getFieldDefinitionByIdentifier(contentType.id, contentFieldIdentifier)
                .then((parsedResponse) => parsedResponse.json())
                .catch(() => {
                    contentErrorCallback(
                        Translator.trans(
                            /*@Desc("Cannot get content type by identifier")*/ 'cannot_get_content_type_identifier.message',
                            {},
                            'ibexa_multi_file_upload',
                        ),
                    );
                })
                .then((parsedResponse) => {
                    const fieldDefinition = parsedResponse.FieldDefinition;

                    if (fieldDefinition.fieldType === 'ezimage') {
                        fileValue.alternativeText = data.file.name;
                    }

                    const fields = [
                        { fieldDefinitionIdentifier: mapping.nameFieldIdentifier, fieldValue: data.file.name },
                        { fieldDefinitionIdentifier: contentFieldIdentifier, fieldValue: fileValue },
                    ];

                    const struct = {
                        ContentCreate: {
                            ContentType: { _href: contentType._href },
                            mainLanguageCode: languageCode ?? parentInfo.language,
                            LocationCreate: { ParentLocation: { _href: parentLocation }, sortField: 'PATH', sortOrder: 'ASC' },
                            Section: null,
                            alwaysAvailable: true,
                            remoteId: null,
                            modificationDate: new Date().toISOString(),
                            fields: { field: fields },
                        },
                    };

                    return struct;
                })
                .catch(() => {
                    contentErrorCallback(
                        Translator.trans(
                            /*@Desc("Cannot create content structure")*/ 'cannot_create_content_structure.message',
                            {},
                            'ibexa_multi_file_upload',
                        ),
                    );
                });
        })
        .catch(() => {
            contentErrorCallback(
                Translator.trans(
                    /*@Desc("Cannot create content structure")*/ 'cannot_create_content_structure.message',
                    {},
                    'ibexa_multi_file_upload',
                ),
            );
        });
};
const createDraft = (struct, requestEventHandlers) => {
    const { instanceUrl, token, siteaccess, accessToken } = getRestInfo();
    const xhr = new XMLHttpRequest();
    const body = JSON.stringify(struct);
    const headers = getRequestHeaders({
        token,
        siteaccess,
        accessToken,
        extraHeaders: {
            Accept: 'application/vnd.ibexa.api.Content+json',
            'Content-Type': 'application/vnd.ibexa.api.ContentCreate+json',
        },
    });

    return new Promise((resolve, reject) => {
        xhr.open('POST', `${instanceUrl}/api/ibexa/v2/content/objects`, true);

        xhr.onreadystatechange = handleOnReadyStateChange.bind(null, xhr, resolve, reject);

        if (requestEventHandlers && Object.keys(requestEventHandlers).length) {
            const uploadEvents = requestEventHandlers.upload;

            if (uploadEvents && Object.keys(uploadEvents).length) {
                xhr.upload.onabort = uploadEvents.onabort;
                xhr.upload.onerror = reject;
                xhr.upload.onload = uploadEvents.onload;
                xhr.upload.onprogress = uploadEvents.onprogress;
                xhr.upload.ontimeout = uploadEvents.ontimeout;
            }

            xhr.onerror = reject;
            xhr.onloadstart = requestEventHandlers.onloadstart;
        }

        for (const headerType in headers) {
            if (Object.prototype.hasOwnProperty.call(headers, headerType)) {
                xhr.setRequestHeader(headerType, headers[headerType]);
            }
        }

        xhr.send(body);
    });
};
const publishDraft = (data) => {
    if (!data?.Content) {
        return Promise.reject('Cannot publish content based on an uploaded file');
    }

    const { instanceUrl, token, siteaccess, accessToken } = getRestInfo();
    const request = new Request(`${instanceUrl}${data.Content.CurrentVersion.Version._href}`, {
        method: 'POST',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                'X-HTTP-Method-Override': 'PUBLISH',
            },
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    return fetch(request).then(handleRequestResponse);
};
const canCreateContent = (file, parentInfo, config) => {
    if (!Object.prototype.hasOwnProperty.call(config, 'contentCreatePermissionsConfig') || !config.contentCreatePermissionsConfig) {
        return true;
    }

    const contentTypeConfig = detectContentTypeMapping(file, parentInfo, config);

    return config.contentCreatePermissionsConfig[contentTypeConfig.contentTypeIdentifier];
};
const getMaxFileSize = (file, parentInfo, config) => {
    const { maxFileSize: contentMaxFileSize } = detectContentTypeMapping(file, parentInfo, config);

    return contentMaxFileSize || config.maxFileSize;
};

export const checkCanUpload = (file, parentInfo, config, errorCallback) => {
    const errorMsgs = [];
    const Translator = getTranslator();
    const locationMapping = config.locationMappings.find((item) => item.contentTypeIdentifier === parentInfo.contentTypeIdentifier);
    const maxFileSize = getMaxFileSize(file, parentInfo, config);

    if (!canCreateContent(file, parentInfo, config)) {
        errorMsgs.push(
            Translator.trans(
                /*@Desc("You do not have permission to create this Content item")*/ 'disallowed_content_type.message',
                {},
                'ibexa_multi_file_upload',
            ),
        );
    }

    if (!checkFileTypeAllowed(file, locationMapping)) {
        errorMsgs.push(Translator.trans(/*@Desc("File type is not allowed")*/ 'disallowed_type.message', {}, 'ibexa_multi_file_upload'));
    }

    if (file.size > maxFileSize) {
        errorMsgs.push(Translator.trans(/*@Desc("File size is not allowed")*/ 'disallowed_size.message', {}, 'ibexa_multi_file_upload'));
    }

    if (errorMsgs.length) {
        errorCallback(errorMsgs);
        return false;
    }

    return true;
};
export const createFileStruct = (file, params, contentErrorCallback) => {
    return new Promise(readFile.bind(new FileReader(), file)).then((fileData) => prepareStruct(params, fileData, contentErrorCallback));
};
export const publishFile = (data, requestEventHandlers, successCallback, contentErrorCallback) => {
    createDraft(data, requestEventHandlers)
        .then(publishDraft)
        .then(successCallback)
        .catch(() => {
            const Translator = getTranslator();

            contentErrorCallback(
                Translator.trans(
                    /*@Desc("An error occurred while publishing a file")*/ 'general.error.message',
                    {},
                    'ibexa_multi_file_upload',
                ),
            );
        });
};
export const deleteFile = (struct, callback, contentErrorCallback) => {
    const { instanceUrl, token, siteaccess, accessToken } = getRestInfo();
    const request = new Request(`${instanceUrl}${struct.Content._href}`, {
        method: 'DELETE',
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
        }),
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
    });

    fetch(request)
        .then(handleRequestResponse)
        .then(callback)
        .catch(() => {
            const Translator = getTranslator();

            contentErrorCallback(
                Translator.trans(
                    /*@Desc("An error occurred while deleting a file")*/ 'delete.error.message',
                    {},
                    'ibexa_multi_file_upload',
                ),
            );
        });
};
