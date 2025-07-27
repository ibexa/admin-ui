import React from 'react';
import PropTypes from 'prop-types';

import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

const UrlIcon = ({ cssClass = '', name = null, customPath = null }) => {
    const linkHref = customPath && customPath !== '' ? customPath : getIconPath(name);

    return (
        <svg className={cssClass}>
            <use xlinkHref={linkHref} />
        </svg>
    );
};

UrlIcon.propTypes = {
    cssClass: PropTypes.string,
    name: PropTypes.string,
    customPath: PropTypes.string,
};

export default UrlIcon;
