export const findMarkedLocation = (loadedLocationsMap, markedLocationId) => {
    const markedLocation = loadedLocationsMap.find(({ parentLocationId }) => parentLocationId === markedLocationId);

    if (markedLocation) {
        return markedLocation;
    } else if (!loadedLocationsMap.length) {
        return {};
    }

    const lastLoadedLocation = loadedLocationsMap[loadedLocationsMap.length - 1];

    return lastLoadedLocation.subitems.find(({ location }) => location.id === markedLocationId) ?? {};
};
