export const checkIsSelectable = ({ location, contentTypesMap, allowedContentTypes, containersOnly }) => {
    const contentType = contentTypesMap[location.ContentInfo.Content.ContentType._href];
    const { isContainer, identifier } = contentType;
    const isAllowedContentType = allowedContentTypes?.includes(identifier) ?? true;

    return (!containersOnly || isContainer) && isAllowedContentType;
};

export const checkIsSelected = ({ location, selectedLocations }) =>
    selectedLocations.some((selectedLocation) => selectedLocation.location.id === location.id);

export const checkIsSelectionBlocked = ({ location, selectedLocations, multipleItemsLimit }) =>
    multipleItemsLimit !== 0 && selectedLocations.length >= multipleItemsLimit && !checkIsSelected({ location, selectedLocations });
