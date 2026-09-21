import { ReactElement } from 'react';

import { PopupMenuItemData } from './popup.menu';

interface PopupMenuGroupProps<T extends PopupMenuItemData> {
    onItemClick: (item: T) => void;
    items?: T[];
    filterText?: string;
}

declare const PopupMenuGroup: <T extends PopupMenuItemData = PopupMenuItemData>(props: PopupMenuGroupProps<T>) => ReactElement;

export default PopupMenuGroup;
