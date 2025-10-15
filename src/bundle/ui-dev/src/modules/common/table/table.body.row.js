import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const TableBodyRow = ({ extraClasses = '', children = null, isSelectable = false, isNotSelectable = false, onClick = () => {} }) => {
    const className = createCssClassNames({
        'ibexa-table__row': true,
        'ibexa-table__row--selectable': isSelectable,
        'ibexa-table__row--not-selectable': isNotSelectable,
        [extraClasses]: true,
    });

    return (
        <tr className={className} onClick={onClick}>
            {children}
        </tr>
    );
};

TableBodyRow.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
    isSelectable: PropTypes.bool,
    isNotSelectable: PropTypes.bool,
    onClick: PropTypes.func,
};

export default TableBodyRow;
