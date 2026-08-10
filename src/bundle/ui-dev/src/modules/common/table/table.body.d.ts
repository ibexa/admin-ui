import { FC, ReactNode } from 'react';

interface TableBodyProps {
    extraClasses?: string;
    children?: ReactNode;
}

declare const TableBody: FC<TableBodyProps>;

export default TableBody;
