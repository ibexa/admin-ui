import React from 'react';
import PropTypes from 'prop-types';

const ViewColumnsTogglerListElement = ({ label, isColumnVisible, toggleColumnVisibility, columnKey }) => {
    return (
        <li className="ibexa-popup-menu__item c-view-columns-toggler-list-element">
            <button className="ibexa-popup-menu__item-content" type="button" onClick={() => toggleColumnVisibility(columnKey)}>
                <input
                    className="form-check-input ibexa-input ibexa-input--checkbox"
                    type="checkbox"
                    checked={isColumnVisible}
                    readOnly={true}
                />
                <label className="form-check-label c-view-columns-toggler-list-element__label">{label}</label>
            </button>
        </li>
    );
};

ViewColumnsTogglerListElement.propTypes = {
    label: PropTypes.string.isRequired,
    columnKey: PropTypes.string.isRequired,
    isColumnVisible: PropTypes.bool.isRequired,
    toggleColumnVisibility: PropTypes.func.isRequired,
};

export default ViewColumnsTogglerListElement;
