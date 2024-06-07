import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';
import { SortingContext, SortOrderContext, SORTING_OPTIONS } from '../../universal.discovery.module';

const SortSwitcher = ({ isDisabled }) => {
    const [sorting, setSorting] = useContext(SortingContext);
    const [sortOrder, setSortOrder] = useContext(SortOrderContext);
    const selectedOption = SORTING_OPTIONS.find((option) => option.sortClause === sorting && option.sortOrder === sortOrder);
    const onOptionClick = (option) => {
        setSorting(option.sortClause);
        setSortOrder(option.sortOrder);
    };

    return (
        <div className="c-sort-switcher">
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
};

SortSwitcher.defaultProps = {
    isDisabled: false,
};

export const SortSwitcherMenuButton = {
    id: 'sort-switcher',
    priority: 20,
    component: SortSwitcher,
};

export default SortSwitcher;
