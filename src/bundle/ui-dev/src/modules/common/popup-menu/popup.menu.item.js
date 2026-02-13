import React from 'react';
import PropTypes from 'prop-types';

import { showItem } from './popup.menu.helper';

const PopupMenuItem = ({ item, filterText = '', onItemClick }) => {
    if (!showItem(item, filterText)) {
        return null;
    }

    return (
        <div className="c-popup-menu__item">
            <button
                type="button"
                className="c-popup-menu__item-content"
                disabled={item.disabled ?? false}
                onClick={() => onItemClick(item)}
            >
                <span className="c-popup-menu__item-label">{item.label}</span>
            </button>
        </div>
    );
};

PopupMenuItem.propTypes = {
    item: PropTypes.shape({
        disabled: PropTypes.bool,
        label: PropTypes.string.isRequired,
    }).isRequired,
    onItemClick: PropTypes.func.isRequired,
    filterText: PropTypes.string,
};

export default PopupMenuItem;
