import React, { useContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';

const SidebarHeader = ({ children }) => {
    return (
        <div className="c-dwb-sidebar-header">
            {children}
        </div>
    );
};

SidebarHeader.propTypes = {
    children: PropTypes.any.isRequired,
};

SidebarHeader.defaultProps = {};

export default SidebarHeader;
