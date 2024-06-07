import React from 'react';
import PropTypes from 'prop-types';
import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';

import { VIEW_MODE_TABLE, VIEW_MODE_GRID } from '../../sub.items.module';

const { Translator } = window;

const ViewSwitcherComponent = ({ onViewChange, activeView, isDisabled }) => {
    let componentClassName = 'c-view-switcher';

    if (isDisabled) {
        componentClassName = `${componentClassName} ${componentClassName}--disabled`;
    }

    const viewLabel = Translator.trans(/*@Desc("View")*/ 'view_switcher.view', {}, 'ibexa_sub_items');
    const switchView = ({ value }) => {
        onViewChange(value);
    };
    const viewOptions = [
        {
            iconName: 'view-list',
            label: Translator.trans(/*@Desc("List view")*/ 'view_switcher.list_view', {}, 'ibexa_sub_items'),
            value: VIEW_MODE_TABLE,
        },
        {
            iconName: 'view-grid',
            label: Translator.trans(/*@Desc("Grid view")*/ 'view_switcher.grid_view', {}, 'ibexa_sub_items'),
            value: VIEW_MODE_GRID,
        },
    ];
    const selectedOption = viewOptions.find((option) => option.value === activeView);

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
