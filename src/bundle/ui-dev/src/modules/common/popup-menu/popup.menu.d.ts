import { ReactElement, ReactNode } from 'react';

export interface PopupMenuItemData {
    label: string;
    value: string | number;
    id?: string | number;
    disabled?: boolean;
}

export interface PopupMenuGroupData<T extends PopupMenuItemData = PopupMenuItemData> {
    id: string;
    key: string;
    items: T[];
}

interface PopupMenuProps<T extends PopupMenuItemData> {
    referenceElement?: HTMLElement;
    extraClasses?: string;
    footer?: ReactNode;
    items?: PopupMenuGroupData<T>[];
    onClose?: () => void;
    onItemClick?: (item: T) => void;
    positionOffset?: (
        referenceElement: HTMLElement,
        verticalPosition: 'top' | 'bottom',
        horizontalPosition: 'left' | 'right',
    ) => { x: number; y: number };
    scrollContainer?: HTMLElement;
}

declare const PopupMenu: <T extends PopupMenuItemData = PopupMenuItemData>(props: PopupMenuProps<T>) => ReactElement;

export default PopupMenu;
