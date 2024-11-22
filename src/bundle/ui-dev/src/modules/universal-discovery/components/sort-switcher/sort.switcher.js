import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';
import { SortingContext, SortOrderContext, SORTING_OPTIONS } from '../../universal.discovery.module';

const SortSwitcher = ({ isDisabled = false, disabledConfig = null }) => {
    const [sorting, setSorting] = useContext(SortingContext);
    const [sortOrder, setSortOrder] = useContext(SortOrderContext);
    const selectedOption = SORTING_OPTIONS.find((option) => option.sortClause === sorting && option.sortOrder === sortOrder);
    const onOptionClick = (option) => {
        setSorting(option.sortClause);
        setSortOrder(option.sortOrder);
    };

    const disabledParams = {};

    if (isDisabled && disabledConfig) {
        disabledParams.title = disabledConfig.disabledInfoTooltipLabel;
    }

    return (
        <div
            ref={(node) => parseTooltip(node)}
            className="c-sort-switcher"
            data-tooltip-container-selector=".c-udw-tab"
            {...disabledParams}
        >
            <SimpleDropdown
                options={SORTING_OPTIONS}
                selectedOption={selectedOption}
                onOptionClick={onOptionClick}
                isDisabled={isDisabled}
                isSwitcher={true}
            />
        </div>
    );
};

SortSwitcher.propTypes = {
    isDisabled: PropTypes.bool,
    disabledConfig: PropTypes.object,
};

export const SortSwitcherMenuButton = {
    id: 'sort-switcher',
    priority: 20,
    component: SortSwitcher,
};

export default SortSwitcher;
