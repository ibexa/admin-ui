import React from 'react';
import PropTypes from 'prop-types';

const PaginationButton = ({
    label = '',
    disabled = false,
    additionalClasses = '',
    buttonAdditionalClasses = '',
    onPageChange = () => {},
    pageIndex = null,
}) => {
    const handleClick = () => {
        if (!disabled && Number.isInteger(pageIndex)) {
            onPageChange(pageIndex);
        }
    };

    let className = `c-pagination-button page-item ${additionalClasses}`;

    className = disabled ? `${className} disabled` : className;

    return (
        <li className={className}>
            <button className={`page-link ${buttonAdditionalClasses}`} onClick={handleClick} type="button">
                {label}
            </button>
        </li>
    );
};

PaginationButton.propTypes = {
    label: PropTypes.string,
    disabled: PropTypes.bool,
    onPageChange: PropTypes.func,
    pageIndex: PropTypes.number,
    additionalClasses: PropTypes.string,
    buttonAdditionalClasses: PropTypes.string,
};

export default PaginationButton;
