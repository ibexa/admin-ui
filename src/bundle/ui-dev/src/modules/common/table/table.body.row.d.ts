import { FC, MouseEventHandler, ReactNode } from 'react';

interface TableBodyRowProps {
    extraClasses?: string;
    children?: ReactNode;
    isSelectable?: boolean;
    isNotSelectable?: boolean;
    onClick?: MouseEventHandler<HTMLTableRowElement>;
}

declare const TableBodyRow: FC<TableBodyRowProps>;

export default TableBodyRow;
