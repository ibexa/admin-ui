import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';

export const SIZES = {
    SMALL: 'small',
    MEDIUM: 'medium',
    LARGE: 'large',
};

const Spinner = ({ size }) => {
    const className = createCssClassNames({
        'c-spinner': true,
        [`c-spinner--${size}`]: true,
    });

    return <div className={className} />;
};

Spinner.propTypes = {
    size: PropTypes.oneOf(Object.values(SIZES)),
};

Spinner.defaultProps = {
    size: SIZES.MEDIUM,
};

export default Spinner;
