import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { MultipleConfigContext } from '../../../universal-discovery/universal.discovery.module';

const SelectionToggler = ({ itemId, isHidden, isSelected }) => {
    const [multiple] = useContext(MultipleConfigContext);
    // const isSelected = selectedItemsIds.some((selectedItemId) => selectedItemId === itemId);
    const className = createCssClassNames({
        'c-udw-toggle-selection ibexa-input': true,
        'ibexa-input--checkbox': multiple,
        'ibexa-input--radio': !multiple,
    });
    const inputType = multiple ? 'checkbox' : 'radio';
    // const toggleSelection = () => {
    //     const action = isSelected ? { type: 'UNSELECT_ITEM', itemId } : { type: 'SELECT_ITEM', itemId };

    //     dispatchSelectedItemsAction(action);
    // };
    // console.log(isSelected, isMultipleSelection, selectedItemsIds,itemId)

    return <input type={inputType} className={className} checked={isSelected} disabled={isHidden} readOnly={true}/>;
};

SelectionToggler.propTypes = {
    itemId: PropTypes.any.isRequired,
    isSelected: PropTypes.bool,
    isHidden: PropTypes.bool,
};

SelectionToggler.defaultProps = {
    isHidden: false,
};

export default SelectionToggler;
