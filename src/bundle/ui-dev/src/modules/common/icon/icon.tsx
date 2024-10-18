import React from 'react';

import { isExternalInstance } from '@ibexa-admin-ui-helpers/context.helper';
import { createCssClassNames } from '../helpers/css.class.names';

import UrlIcon from './urlIcon';
import InculdedIcon from './inculdedIcon';

interface BaseIconProps {
    extraClasses?: string;
    useIncludedIcon?: boolean;
    defaultIconName?: string | null;
}
interface NameIconProps extends BaseIconProps {
    name: string;
    customPath?: string | null;
}
interface CustomPathIconProps extends BaseIconProps {
    name?: string | null;
    customPath: string;
}

type IconProps = NameIconProps | CustomPathIconProps;

const Icon = ({
    name = null,
    customPath = null,
    extraClasses = '',
    useIncludedIcon = false,
    defaultIconName = 'about-info',
}: IconProps) => {
    const cssClass = createCssClassNames({
        'ibexa-icon': true,
        [extraClasses]: true,
    });

    const isIconIncluded = useIncludedIcon || isExternalInstance();

    if (isIconIncluded) {
        return <InculdedIcon cssClass={cssClass} name={name} defaultIconName={defaultIconName} />;
    }

    return <UrlIcon cssClass={cssClass} name={name} customPath={customPath} />;
};

export default Icon;
