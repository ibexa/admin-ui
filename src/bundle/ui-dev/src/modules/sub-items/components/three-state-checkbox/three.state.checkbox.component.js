import React from 'react';
import PropTypes from 'prop-types';

const ThreeStateCheckboxComponent = ({ indeterminate, ...restOfProps }) => (
    <input
        {...restOfProps}
        type="checkbox"
        ref={(input) => {
            if (input) {
                input.indeterminate = indeterminate;
            }
        }}
    />
);

ThreeStateCheckboxComponent.propTypes = {
    indeterminate: PropTypes.bool,
};

ThreeStateCheckboxComponent.defaultProps = {
    indeterminate: false,
};

export default ThreeStateCheckboxComponent;
