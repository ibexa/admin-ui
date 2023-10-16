import React, { useContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import GridViewItem from './grid.view.item';

const GridView = ({ items }) => {
    return (
        <div className="ibexa-grid-view">
            {items.map((item) => (
                <GridViewItem key={item.itemId} {...item} />
            ))}
        </div>
    );
};

GridView.propTypes = {
    items: PropTypes.array.isRequired,
};

GridView.defaultProps = {};

export default GridView;
