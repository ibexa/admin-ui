import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const TableBody = ({ extraClasses = '', children = null }) => {
    const className = createCssClassNames({
        'ibexa-table__body': true,
        [extraClasses]: true,
    });

    return <tbody className={className}>{children}</tbody>;
};

TableBody.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
};

export default TableBody;
