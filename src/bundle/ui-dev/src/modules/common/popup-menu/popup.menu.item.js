import React from 'react';
import PropTypes from 'prop-types';

import { showItem } from './popup.menu.helper';

const PopupMenuItem = ({ item, filterText, onItemClick }) => {
    if (!showItem(item, filterText)) {
        return null;
    }

    return (
        <div className="c-popup-menu__item">
            <button type="button" className="c-popup-menu__item-content" onClick={() => onItemClick(item)}>
                <span className="c-popup-menu__item-label">{item.label}</span>
            </button>
        </div>
    );
};

PopupMenuItem.propTypes = {
    item: PropTypes.shape({
        label: PropTypes.string.isRequired,
    }).isRequired,
    onItemClick: PropTypes.func.isRequired,
    filterText: PropTypes.string,
};

PopupMenuItem.defaultProps = {
    filterText: '',
};

export default PopupMenuItem;
