import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const TableHeadRow = ({ extraClasses, children }) => {
    const className = createCssClassNames({
        'ibexa-table__head-row': true,
        [extraClasses]: true,
    });

    return <tr className={className}>{children}</tr>;
};

TableHeadRow.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
};

TableHeadRow.defaultProps = {
    extraClasses: '',
    children: null,
};

export default TableHeadRow;
