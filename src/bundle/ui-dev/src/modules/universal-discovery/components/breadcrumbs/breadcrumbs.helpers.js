export const getLoadedLocationsLimitedMap = (loadedLocationsFullMap, activeLocationId) => {
    const itemIndex = loadedLocationsFullMap.findIndex(({ parentLocationId }) => parentLocationId === activeLocationId);

    if (itemIndex === -1) {
        return [];
    }

    return loadedLocationsFullMap.slice(0, itemIndex + 1);
};
