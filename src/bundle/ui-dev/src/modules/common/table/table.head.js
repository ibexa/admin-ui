import React from 'react';
import PropTypes from 'prop-types';

const TableHead = ({ extraClasses = '', children = null }) => {
    return <thead className={extraClasses}>{children}</thead>;
};

TableHead.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
};

export default TableHead;
