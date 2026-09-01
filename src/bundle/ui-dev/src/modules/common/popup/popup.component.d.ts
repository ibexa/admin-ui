import { FC, MouseEvent, ReactNode } from 'react';

import { ButtonSize, ButtonType } from '@ids-components/components/Button';

export interface PopupActionBtnConfig {
    label: string;
    onClick?: (event: MouseEvent, hidePopup: () => void) => void;
    disabled?: boolean;
    className?: string;
    preventClose?: boolean;
    size?: ButtonSize;
    type?: ButtonType;
}

interface PopupProps {
    actionBtnsConfig: PopupActionBtnConfig[];
    children: ReactNode;
    isVisible: boolean;
    onClose?: (() => void) | null;
    title?: string | null;
    subtitle?: string | null;
    hasFocus?: boolean;
    size?: 'small' | 'medium' | 'large';
    noHeader?: boolean;
    noFooter?: boolean;
    noCloseBtn?: boolean;
    noKeyboard?: boolean;
    extraClasses?: string;
    showTooltip?: boolean;
    subheader?: ReactNode;
    controlZIndex?: boolean;
}

declare const Popup: FC<PopupProps>;

export default Popup;
