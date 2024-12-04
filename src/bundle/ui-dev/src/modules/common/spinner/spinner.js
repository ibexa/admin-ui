import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';

export const SIZES = {
    SMALL: 'small',
    MEDIUM: 'medium',
    LARGE: 'large',
};

export const COLOR_VARIATNS = {
    PRIMARY: 'primary',
    LIGHT: 'light',
};

const Spinner = ({ size, colorVariant }) => {
    const className = createCssClassNames({
        'c-spinner': true,
        [`c-spinner--${size}`]: true,
        [`c-spinner--${colorVariant}`]: true,
    });

    return <div className={className} />;
};

Spinner.propTypes = {
    size: PropTypes.oneOf(Object.values(SIZES)),
    colorVariant: PropTypes.oneOf(Object.values(COLOR_VARIATNS)),
};

Spinner.defaultProps = {
    size: SIZES.MEDIUM,
    colorVariant: COLOR_VARIATNS.PRIMARY,
};

export default Spinner;
