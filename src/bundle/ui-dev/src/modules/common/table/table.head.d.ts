import { FC, ReactNode } from 'react';

interface TableHeadProps {
    extraClasses?: string;
    children?: ReactNode;
}

declare const TableHead: FC<TableHeadProps>;

export default TableHead;
