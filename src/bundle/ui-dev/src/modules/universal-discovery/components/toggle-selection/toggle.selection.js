import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { SelectedLocationsContext } from '../../universal.discovery.module';

const ToggleSelection = ({ multiple, location, isHidden }) => {
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const isSelected = selectedLocations.some((selectedItem) => selectedItem.location.id === location.id);
    const className = createCssClassNames({
        'c-udw-toggle-selection ibexa-input': true,
        'ibexa-input--checkbox': multiple,
        'c-udw-toggle-selection--hidden': isHidden,
    });
    const toggleSelection = () => {
        const action = isSelected ? { type: 'REMOVE_SELECTED_LOCATION', id: location.id } : { type: 'ADD_SELECTED_LOCATION', location };

        dispatchSelectedLocationsAction(action);
    };

    if (!multiple) {
        return null;
    }

    return <input type="checkbox" className={className} checked={isSelected} disabled={isHidden} onChange={toggleSelection} />;
};

ToggleSelection.propTypes = {
    location: PropTypes.object.isRequired,
    multiple: PropTypes.bool.isRequired,
    isHidden: PropTypes.bool,
};

ToggleSelection.defaultProps = {
    isHidden: false,
};

export default ToggleSelection;
