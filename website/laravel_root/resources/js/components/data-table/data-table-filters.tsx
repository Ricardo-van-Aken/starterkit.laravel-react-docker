import { Input } from '@/components/ui/input';
import { DataTableFacetedFilter } from './data-table-faceted-filter';
import { DataTableTimeframeFilter } from './data-table-timeframe-filter';
import { FilterConfig } from './types';

interface DataTableFiltersProps {
    table: {
        state: {
            filters: Record<string, any>;
        };
        onFilterChange: (key: string, value: any) => void;
    };
    filters?: FilterConfig[];
}

export function DataTableFilters({ table, filters }: DataTableFiltersProps) {
    if (!filters?.length) return null;

    return (
        <div className="flex items-center space-x-2">
            {filters.map((filter) => {
                const value = table.state.filters[filter.key];

                switch (filter.type) {
                    case 'faceted':
                        return (
                            <DataTableFacetedFilter
                                key={filter.key}
                                title={filter.label}
                                options={filter.options || []}
                                selectedValues={value || []}
                                onSelect={(values: string[]) => table.onFilterChange(filter.key, values)}
                                onClear={() => table.onFilterChange(filter.key, [])}
                            />
                        );
                    case 'timeframe':
                        return (
                            <DataTableTimeframeFilter
                                key={filter.key}
                                title={filter.label}
                                value={value}
                                onSelect={(val: string | null) => table.onFilterChange(filter.key, val || '')}
                            />
                        );
                    case 'text':
                        return (
                            <Input
                                key={filter.key}
                                placeholder={filter.placeholder || filter.label}
                                className="h-8 w-[150px] lg:w-[250px]"
                                value={value || ''}
                                onChange={(e) => table.onFilterChange(filter.key, e.target.value)}
                            />
                        );
                    default:
                        return null;
                }
            })}
        </div>
    );
}
