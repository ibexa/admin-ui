import { FC, ReactNode } from 'react';

interface TableHeadCellProps {
    extraClasses?: string;
    wrapperExtraClasses?: string;
    children?: ReactNode;
    sortColumnName?: string | null;
    hasCheckbox?: boolean;
    hasIcon?: boolean;
    isCloseLeft?: boolean;
    isCenterContent?: boolean;
}

declare const TableHeadCell: FC<TableHeadCellProps>;

export default TableHeadCell;
