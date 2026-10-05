import React from 'react';
import PropTypes from 'prop-types';

import { showItem } from './popup.menu.helper';

const PopupMenuItem = ({ item, filterText = '', onItemClick }) => {
    if (!showItem(item, filterText)) {
        return null;
    }

    const label = <span className="c-popup-menu__item-label">{item.label}</span>;
    const isDisabled = item.disabled ?? false;

    return (
        <div className="c-popup-menu__item">
            {item.href && !isDisabled ? (
                <a
                    className="c-popup-menu__item-content"
                    href={item.href}
                    target={item.target}
                    rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                    onClick={() => onItemClick(item)}
                >
                    {label}
                </a>
            ) : (
                <button type="button" className="c-popup-menu__item-content" disabled={isDisabled} onClick={() => onItemClick(item)}>
                    {label}
                </button>
            )}
        </div>
    );
};

PopupMenuItem.propTypes = {
    item: PropTypes.shape({
        disabled: PropTypes.bool,
        href: PropTypes.string,
        label: PropTypes.string.isRequired,
        target: PropTypes.string,
    }).isRequired,
    onItemClick: PropTypes.func.isRequired,
    filterText: PropTypes.string,
};

export default PopupMenuItem;
