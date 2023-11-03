import React, { useContext, useEffect } from 'react';
import PropTypes from 'prop-types';

import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

import {
    UDWContext,
    SelectedLocationsContext,
    RestInfoContext,
    MultipleConfigContext,
    ContainersOnlyContext,
    AllowedContentTypesContext,
} from '../../universal.discovery.module';
import { findLocationsById } from '../../services/universal.discovery.service';
import ToggleSelection from '../toggle-selection/toggle.selection';

const { ibexa, document } = window;

const TreeItemToggleSelection = ({ locationId, isContainer, contentTypeIdentifier }) => {
    const isUDW = useContext(UDWContext);

    useEffect(() => {
        parseTooltip(document.querySelector('.c-list'));
    }, []);

    if (!isUDW) {
        return null;
    }

    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const restInfo = useContext(RestInfoContext);
    const isNotSelectable =
        (containersOnly && !isContainer) || (allowedContentTypes && !allowedContentTypes.includes(contentTypeIdentifier));
    const location = {
        id: locationId,
    };
    const dispatchSelectedLocationsActionWrapper = (action) => {
        if (action.location !== undefined) {
            findLocationsById({ ...restInfo, id: action.location.id }, ([selectedLocation]) => {
                dispatchSelectedLocationsAction({ ...action, location: selectedLocation });
            });
        } else {
            dispatchSelectedLocationsAction(action);
        }
    };

    return (
        <SelectedLocationsContext.Provider value={[selectedLocations, dispatchSelectedLocationsActionWrapper]}>
            <ToggleSelection location={location} multiple={multiple} isHidden={isNotSelectable} />
            {isNotSelectable && <div className="c-list-item__prefix-actions-item-empty" />}
        </SelectedLocationsContext.Provider>
    );
};

ibexa.addConfig(
    'adminUiConfig.contentTreeWidget.secondaryItemActions',
    [
        {
            id: 'toggle-selection-button',
            priority: 30,
            component: TreeItemToggleSelection,
        },
    ],
    true,
);

TreeItemToggleSelection.propTypes = {
    locationId: PropTypes.number.isRequired,
    isContainer: PropTypes.bool.isRequired,
    contentTypeIdentifier: PropTypes.string.isRequired,
};

export default TreeItemToggleSelection;
