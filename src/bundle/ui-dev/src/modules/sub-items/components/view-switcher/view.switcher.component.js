import React from 'react';
import PropTypes from 'prop-types';
import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';
import { getViewSwitcherOptions } from '../../services/view.registry';

const ViewSwitcherComponent = ({ onViewChange, activeView, isDisabled }) => {
    let componentClassName = 'c-view-switcher';

    if (isDisabled) {
        componentClassName = `${componentClassName} ${componentClassName}--disabled`;
    }

    const switchView = ({ value }) => {
        onViewChange(value);
    };
    
    const viewLabel = Translator.trans(/*@Desc("View")*/ 'view_switcher.view', {}, 'ibexa_sub_items');
    const viewOptions = getViewSwitcherOptions();
    const selectedOption = viewOptions.find((option) => option.value === activeView) || viewOptions[0];

    return (
        <div className={componentClassName}>
            <SimpleDropdown
                options={viewOptions}
                selectedOption={selectedOption}
                onOptionClick={switchView}
                selectedItemLabel={viewLabel}
                isSwitcher={true}
            />
        </div>
    );
};

ViewSwitcherComponent.propTypes = {
    onViewChange: PropTypes.func.isRequired,
    activeView: PropTypes.string.isRequired,
    isDisabled: PropTypes.bool.isRequired,
};

export default ViewSwitcherComponent;
