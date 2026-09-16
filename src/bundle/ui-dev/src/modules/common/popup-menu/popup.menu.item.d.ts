import { ReactElement } from 'react';

import { PopupMenuItemData } from './popup.menu';

interface PopupMenuItemProps<T extends PopupMenuItemData> {
    item: T;
    onItemClick: (item: T) => void;
    filterText?: string;
}

declare const PopupMenuItem: <T extends PopupMenuItemData = PopupMenuItemData>(props: PopupMenuItemProps<T>) => ReactElement;

export default PopupMenuItem;
