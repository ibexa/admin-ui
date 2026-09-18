import { FC } from 'react';

interface PopupMenuSearchProps {
    numberOfItems: number;
    setFilterText: (filterText: string) => void;
    filterText?: string;
}

declare const PopupMenuSearch: FC<PopupMenuSearchProps>;

export default PopupMenuSearch;
