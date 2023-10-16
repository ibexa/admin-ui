import React, { useContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import ListViewHeader from './list.view.header';
import ListViewItem from './list.view.item';

const ListView = ({ items }) => {
    return (
        <table className="ibexa-table table">
            <ListViewHeader />
            <tbody className="ibexa-table__body">
                {items.map((item) => (
                    <ListViewItem key={item.id} item={item} />
                ))}
            </tbody>
        </table>
    );
};

ListView.propTypes = {
    items: PropTypes.array.isRequired,
};

ListView.defaultProps = {};

export default ListView;
