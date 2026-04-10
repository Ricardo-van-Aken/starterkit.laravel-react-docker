import { Input } from '@/components/ui/input';
import { DataTableFacetedFilter } from './DataTableFacetedFilter';
import { DataTableTimeframeFilter } from './DataTableTimeframeFilter';
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
                                selectedValues={
                                    Array.isArray(value) 
                                        ? new Set(value) 
                                        : value && value !== 'all' 
                                            ? new Set([value]) 
                                            : new Set()
                                }
                                onSelect={(val: string) => {
                                    const currentValues = Array.isArray(value) ? value : (value && value !== 'all' ? [value] : []);
                                    const newValues = currentValues.includes(val)
                                        ? currentValues.filter((v) => v !== val)
                                        : [...currentValues, val];
                                    
                                    table.onFilterChange(filter.key, newValues.length > 0 ? newValues : 'all');
                                }}
                                onClear={() => table.onFilterChange(filter.key, 'all')}
                            />
                        );
                    case 'timeframe':
                        return (
                            <DataTableTimeframeFilter
                                key={filter.key}
                                title={filter.label}
                                value={value}
                                onSelect={(val: string | null) => table.onFilterChange(filter.key, val || 'all')}
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
