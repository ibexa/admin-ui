import { useCallback, useContext } from 'react';

import { checkIsSelectable, checkIsSelected, checkIsSelectionBlocked } from '../helpers/selected.locations.helper';
import {
    AllowedContentTypesContext,
    ContainersOnlyContext,
    ContentTypesMapContext,
    MultipleConfigContext,
    SelectedLocationsContext,
} from '../universal.discovery.module';

export const useSelectedLocationsHelpers = () => {
    const [, multipleItemsLimit] = useContext(MultipleConfigContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const [selectedLocations] = useContext(SelectedLocationsContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const checkIsSelectableWrapped = useCallback(
        (location) => checkIsSelectable({ location, contentTypesMap, allowedContentTypes, containersOnly }),
        [contentTypesMap, allowedContentTypes, containersOnly],
    );
    const checkIsSelectedWrapped = useCallback((location) => checkIsSelected({ location, selectedLocations }), [selectedLocations]);
    const checkIsSelectionBlockedWrapped = useCallback(
        (location) => checkIsSelectionBlocked({ location, selectedLocations, multipleItemsLimit }),
        [selectedLocations, multipleItemsLimit],
    );

    return {
        checkIsSelectable: checkIsSelectableWrapped,
        checkIsSelected: checkIsSelectedWrapped,
        checkIsSelectionBlocked: checkIsSelectionBlockedWrapped,
    };
};
