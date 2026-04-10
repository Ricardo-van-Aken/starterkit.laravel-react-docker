import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

interface DataTableSortHeaderProps {
    title: string;
    sortKey: string;
    table: {
        state: {
            sort: string;
            direction: 'asc' | 'desc';
        };
        onSort: (key: string) => void;
    };
    className?: string;
}

export function DataTableSortHeader({ title, sortKey, table, className }: DataTableSortHeaderProps) {
    const isActive = table.state.sort === sortKey;

    return (
        <div className={cn('flex items-center space-x-2', className)}>
            <Button
                variant="ghost"
                size="sm"
                className="-ml-3 h-8 data-[state=open]:bg-accent capitalize"
                onClick={() => table.onSort(sortKey)}
            >
                <span>{title}</span>
                {isActive ? (
                    table.state.direction === 'desc' ? (
                        <ArrowDown className="ml-2 h-4 w-4" />
                    ) : (
                        <ArrowUp className="ml-2 h-4 w-4" />
                    )
                ) : (
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                )}
            </Button>
        </div>
    );
}
