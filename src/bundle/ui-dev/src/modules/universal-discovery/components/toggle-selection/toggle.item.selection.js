import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { MultipleConfigContext, SelectedItemsContext } from '../../universal.discovery.module';

const ToggleItemSelection = ({ item, isDisabled, isHidden }) => {
    const { selectedItems } = useContext(SelectedItemsContext);
    const [multiple, multipleItemsLimit] = useContext(MultipleConfigContext);
    const isSelected = selectedItems.some((selectedItem) => selectedItem.type === item.type && selectedItem.id === item.id);
    const isSelectionBlocked = multipleItemsLimit !== 0 && selectedItems.length >= multipleItemsLimit && !isSelected;
    const className = createCssClassNames({
        'c-udw-toggle-selection ibexa-input': true,
        'ibexa-input--checkbox': multiple,
        'ibexa-input--radio': !multiple,
        'c-udw-toggle-selection--hidden': isHidden,
    });
    const inputType = multiple ? 'checkbox' : 'radio';

    return (
        <input
            type={inputType}
            className={className}
            checked={isSelected}
            disabled={isSelectionBlocked || isDisabled || isHidden}
            readOnly={true}
        />
    );
};

ToggleItemSelection.propTypes = {
    item: PropTypes.object.isRequired,
    isHidden: PropTypes.bool,
    isDisabled: PropTypes.bool,
};

ToggleItemSelection.defaultProps = {
    isHidden: false,
    isDisabled: false,
};

export default ToggleItemSelection;
