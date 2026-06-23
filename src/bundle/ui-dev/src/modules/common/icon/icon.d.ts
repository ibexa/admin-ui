import { FC } from 'react';

interface IconProps {
    extraClasses?: string;
    name?: string | null;
    customPath?: string;
    useIncludedIcon?: boolean;
    defaultIconName?: string;
}

declare const Icon: FC<IconProps>;

export default Icon;
