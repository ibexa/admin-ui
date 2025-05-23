import React from 'react';
import PropTypes from 'prop-types';

import PopupMenuItem from './popup.menu.item';
import { showItem } from './popup.menu.helper';

const PopupMenuGroup = ({ items = [], filterText = '', onItemClick }) => {
    const isAnyItemVisible = items.some((item) => showItem(item, filterText));

    if (!isAnyItemVisible) {
        return null;
    }

    return (
        <div className="c-popup-menu__group">
            {items.map((item) => (
                <PopupMenuItem key={item.value} item={item} filterText={filterText} onItemClick={onItemClick} />
            ))}
        </div>
    );
};

PopupMenuGroup.propTypes = {
    items: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
        }),
    ),
    onItemClick: PropTypes.func.isRequired,
    filterText: PropTypes.string,
};

export default PopupMenuGroup;
