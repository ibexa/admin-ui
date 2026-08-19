import { FC, ReactNode } from 'react';

interface TableHeadRowProps {
    extraClasses?: string;
    children?: ReactNode;
}

declare const TableHeadRow: FC<TableHeadRowProps>;

export default TableHeadRow;
