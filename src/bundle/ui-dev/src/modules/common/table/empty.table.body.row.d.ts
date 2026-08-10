import { FC, ReactNode } from 'react';

interface EmptyTableBodyRowProps {
    extraClasses?: string;
    infoText?: string | null;
    actionText?: ReactNode;
    extraActions?: ReactNode;
    emptyTableImageSrc?: string | null;
    colspan?: number;
}

declare const EmptyTableBodyRow: FC<EmptyTableBodyRowProps>;

export default EmptyTableBodyRow;
