import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

const FiltersRow = ({ children, title, extraClasses = '' }) => {
    const className = createCssClassNames({
        'c-filters-row': true,
        [extraClasses]: true,
    });

    return (
        <div className={className}>
            <div className="c-filters-row__title">{title}</div>
            {children}
        </div>
    );
};

FiltersRow.propTypes = {
    children: PropTypes.node.isRequired,
    title: PropTypes.string.isRequired,
    extraClasses: PropTypes.string,
};

export default FiltersRow;
