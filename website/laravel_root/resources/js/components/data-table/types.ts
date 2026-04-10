import * as React from 'react';

export interface ColumnDef<TData, TValue = any> {
    id?: string;
    accessorKey?: keyof TData | string;
    header: string | ((props: { column: ColumnDef<TData, TValue> }) => React.ReactNode);
    cell?: (props: { row: TData; value: TValue }) => React.ReactNode;
    sortable?: boolean;
    className?: string;
    headerClassName?: string;
}

export interface FilterOption {
    label: string;
    value: string;
    icon?: React.ComponentType<{ className?: string }>;
}

export interface FilterConfig {
    key: string;
    label: string;
    type: 'text' | 'select' | 'faceted' | 'timeframe';
    options?: FilterOption[];
    placeholder?: string;
}
