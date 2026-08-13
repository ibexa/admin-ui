import { FC, ReactNode } from 'react';

interface TableProps {
    extraClasses?: string;
    children?: ReactNode;
    isLastColumnSticky?: boolean;
}

declare const Table: FC<TableProps>;

export default Table;
