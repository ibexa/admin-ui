import React from 'react';
import PropTypes from 'prop-types';

import { CheckboxInput } from '@ids-components/components/Checkbox';

const ThreeStateCheckboxComponent = ({ indeterminate = false, ...restOfProps }) => (
    <CheckboxInput {...restOfProps} indeterminate={indeterminate} />
);

ThreeStateCheckboxComponent.propTypes = {
    indeterminate: PropTypes.bool,
};

export default ThreeStateCheckboxComponent;
