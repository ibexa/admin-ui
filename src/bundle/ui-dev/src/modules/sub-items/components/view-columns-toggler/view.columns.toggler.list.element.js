import React from 'react';
import PropTypes from 'prop-types';

import { CheckboxInput } from '@ids-components/components/Checkbox';

const ViewColumnsTogglerListElement = ({ label, isColumnVisible, toggleColumnVisibility, columnKey }) => {
    return (
        <li className="ibexa-popup-menu__item c-view-columns-toggler-list-element">
            <button className="ibexa-popup-menu__item-content" type="button" onClick={() => toggleColumnVisibility(columnKey)}>
                <CheckboxInput
                    className="ids-input ids-input--checkbox c-view-columns-toggler-list-element__checkbox"
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
