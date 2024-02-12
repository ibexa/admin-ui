import React from 'react';
import PropTypes from 'prop-types';

import { getRestInfo } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '../helpers/css.class.names';

import UrlIcon from './urlIcon';
import InculdedIcon from './inculdedIcon';

const Icon = (props) => {
    const { instanceUrl } = getRestInfo();
    const cssClass = createCssClassNames({
        'ibexa-icon': true,
        [props.extraClasses]: true,
    });

    const isIncludedIcon = props.useIncludedIcon || window.origin !== instanceUrl;

    return (
        <>
            {isIncludedIcon ? (
                <InculdedIcon cssClass={cssClass} name={props.name} defaultIconName={props.defaultIconName} />
            ) : (
                <UrlIcon cssClass={cssClass} name={props.name} customPath={props.customPath} />
            )}
        </>
    );
};

Icon.propTypes = {
    extraClasses: PropTypes.string,
    name: PropTypes.string,
    customPath: PropTypes.string,
    useIncludedIcon: PropTypes.bool,
    defaultIconName: PropTypes.string,
};

Icon.defaultProps = {
    customPath: null,
    name: null,
    extraClasses: null,
    useIncludedIcon: false,
    defaultIconName: 'about-info',
};

export default Icon;
