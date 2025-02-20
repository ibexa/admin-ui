import React from 'react';
import PropTypes from 'prop-types';

const TableHead = ({ extraClasses, children }) => {
    return <thead className={extraClasses}>{children}</thead>;
};

TableHead.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
};

TableHead.defaultProps = {
    extraClasses: '',
    children: null,
};

export default TableHead;
