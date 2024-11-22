import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { SelectedItemsContext } from '../../universal.discovery.module';
import { TOGGLE_SELECTED_ITEMS } from '../../hooks/useSelectedItemsReducer';

const ToggleItemSelection = ({ multiple, item, isHidden = false }) => {
    const { selectedItems, dispatchSelectedItemsAction } = useContext(SelectedItemsContext);
    const isSelected = selectedItems.some((selectedItem) => selectedItem.type === item.type && selectedItem.id === item.id);
    const className = createCssClassNames({
        'c-udw-toggle-selection ibexa-input': true,
        'ibexa-input--checkbox': multiple,
        'c-udw-toggle-selection--hidden': isHidden,
    });
    const inputType = multiple ? 'checkbox' : 'radio';

    console.log(isSelected, selectedItems, item.type, item.id);
    return <input type={inputType} className={className} checked={isSelected} disabled={isHidden} readOnly={true} />;
};

ToggleItemSelection.propTypes = {
    item: PropTypes.object.isRequired,
    multiple: PropTypes.bool.isRequired,
    isHidden: PropTypes.bool,
};

export default ToggleItemSelection;
