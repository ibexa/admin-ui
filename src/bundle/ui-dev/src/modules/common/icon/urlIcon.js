import React from 'react';
import PropTypes from 'prop-types';

import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

const UrlIcon = (props) => {
    const linkHref = props.customPath ? props.customPath : getIconPath(props.name);

    return (
        <svg className={props.cssClass}>
            <use xlinkHref={linkHref} />
        </svg>
    );
};

UrlIcon.propTypes = {
    cssClass: PropTypes.string,
    name: PropTypes.string,
    customPath: PropTypes.string,
};

UrlIcon.defaultProps = {
    customPath: null,
    name: null,
    cssClass: '',
};

export default UrlIcon;
