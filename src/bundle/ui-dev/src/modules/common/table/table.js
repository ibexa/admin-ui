import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const Table = ({ extraClasses, children, isLastColumnSticky }) => {
    const className = createCssClassNames({
        'ibexa-table table': true,
        'ibexa-table--last-column-sticky': isLastColumnSticky,
        [extraClasses]: true,
    });

    return <table className={className}>{children}</table>;
};

Table.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
    isLastColumnSticky: PropTypes.bool,
};

Table.defaultProps = {
    extraClasses: '',
    children: null,
    isLastColumnSticky: false,
};

export default Table;
