import { FC, ReactNode } from 'react';

interface TableBodyCellProps {
    extraClasses?: string;
    children?: ReactNode;
    hasCheckbox?: boolean;
    hasActionBtns?: boolean;
    hasIcon?: boolean;
    isCloseLeft?: boolean;
    isCenterContent?: boolean;
}

declare const TableBodyCell: FC<TableBodyCellProps>;

export default TableBodyCell;
