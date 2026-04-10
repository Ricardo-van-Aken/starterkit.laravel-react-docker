import { XCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { DataTableFilters } from './DataTableFilters';
import { FilterConfig } from './types';

interface DataTableToolbarProps {
    table: {
        state: {
            filters: Record<string, any>;
        };
        onFilterChange: (key: string, value: any) => void;
        isFiltered: boolean;
        onClearFilters: () => void;
        [key: string]: any;
    };
    filters?: FilterConfig[];
}

export function DataTableToolbar({ table, filters }: DataTableToolbarProps) {
    return (
        <div className="flex items-center justify-between gap-4 p-4 text-nowrap">
            <div className="flex flex-1 items-center space-x-2">
                <DataTableFilters table={table} filters={filters} />

                {table.isFiltered && (
                    <Button
                        variant="ghost"
                        onClick={table.onClearFilters}
                        className="h-8 px-2 lg:px-3 text-muted-foreground"
                    >
                        Reset
                        <XCircle className="ml-2 h-4 w-4" />
                    </Button>
                )}
            </div>
        </div>
    );
}
