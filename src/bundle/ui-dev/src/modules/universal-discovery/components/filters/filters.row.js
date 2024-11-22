import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

const FiltersRow = ({ children, title, extraClasses }) => {
    const className = createCssClassNames({
        'c-filters__row': true,
        [extraClasses]: true,
    });

    return (
        <div className={className}>
            <div className="c-filters__row-title">{title}</div>
            {children}
        </div>
    );
};

FiltersRow.propTypes = {
    children: PropTypes.node.isRequired,
    extraClasses: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
};

export default FiltersRow;
