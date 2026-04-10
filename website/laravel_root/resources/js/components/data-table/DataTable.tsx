import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { DataTableToolbar } from "./DataTableToolbar";
import { DataTablePagination } from "./DataTablePagination";
import { DataTableSortHeader } from "./DataTableSortHeader";
import { ColumnDef, FilterConfig } from "./types";
import { cn } from "@/lib/utils";

interface DataTableProps<TData> {
    table: {
        data: TData[];
        meta: any;
        state: {
            sort: string;
            direction: 'asc' | 'desc';
            filters: Record<string, any>;
            [key: string]: any;
        };
        onSort: (key: string) => void;
        onFilterChange: (key: string, value: any) => void;
        isFiltered: boolean;
        onClearFilters: () => void;
        [key: string]: any;
    };
    columns: ColumnDef<TData>[];
    filters?: FilterConfig[];
    className?: string;
}

export function DataTable<TData>({ table, columns, filters, className }: DataTableProps<TData>) {
    return (
        <div className="space-y-4">
            <DataTableToolbar table={table} filters={filters} />
            
            <div className={cn("rounded-md border bg-card overflow-hidden", className)}>
                <Table>
                    <TableHeader className="bg-muted/50">
                        <TableRow>
                            {columns.map((column, index) => (
                                <TableHead key={column.id || index} className={column.headerClassName}>
                                    {column.sortable && column.accessorKey ? (
                                        <DataTableSortHeader 
                                            title={typeof column.header === 'string' ? column.header : ''} 
                                            sortKey={column.accessorKey as string} 
                                            table={table}
                                        />
                                    ) : (
                                        typeof column.header === 'function' 
                                            ? column.header({ column }) 
                                            : column.header
                                    )}
                                </TableHead>
                            ))}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {table.data.length ? (
                            table.data.map((row: any, rowIndex: number) => (
                                <TableRow key={row.uuid || row.id || rowIndex}>
                                    {columns.map((column, colIndex) => {
                                        const value = column.accessorKey 
                                            ? (row as any)[column.accessorKey] 
                                            : undefined;
                                        
                                        return (
                                            <TableCell key={colIndex} className={column.className}>
                                                {column.cell 
                                                    ? column.cell({ row, value }) 
                                                    : value}
                                            </TableCell>
                                        );
                                    })}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center text-muted-foreground italic">
                                    No results found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            <DataTablePagination table={table} />
        </div>
    );
}
