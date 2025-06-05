import React from 'react';
import PropTypes from 'prop-types';

import { isExternalInstance } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '../helpers/css.class.names';

import UrlIcon from './urlIcon';
import IncludedIcon from './includedIcon';

const Icon = (props) => {
    const cssClass = createCssClassNames({
        'ibexa-icon': true,
        [props.extraClasses]: true,
    });

    const isIconIncluded = props.useIncludedIcon || isExternalInstance();

    return (
        <>
            {isIconIncluded ? (
                <IncludedIcon cssClass={cssClass} name={props.name} defaultIconName={props.defaultIconName} />
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
