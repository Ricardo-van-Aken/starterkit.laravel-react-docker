import * as React from 'react';

export interface ColumnDef<TData, TValue = any> {
    id?: string;
    accessorKey?: keyof TData | string;
    header: string | ((props: { column: ColumnDef<TData, TValue> }) => React.ReactNode);
    cell?: (props: { row: TData; value: TValue }) => React.ReactNode;
    sortable?: boolean;
    align?: 'left' | 'center' | 'right';
    className?: string;
    headerClassName?: string;
}

export interface FilterOption {
    label: string;
    value: string;
    component?: React.ComponentType<{ value: string; label: string; isSelected: boolean }>;
}

export interface FilterConfig {
    key: string;
    label: string;
    type: 'text' | 'select' | 'faceted' | 'timeframe';
    options?: FilterOption[];
    placeholder?: string;
}
