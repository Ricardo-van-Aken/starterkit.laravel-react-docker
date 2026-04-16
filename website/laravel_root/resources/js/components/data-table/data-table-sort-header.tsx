import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-react';
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
    align?: 'left' | 'center' | 'right';
    className?: string;
}

export function DataTableSortHeader({ title, sortKey, table, align, className }: DataTableSortHeaderProps) {
    const isActive = table.state.sort === sortKey;
    const isRightAligned = align === 'right';

    return (
        <div className={cn('flex items-center space-x-2', isRightAligned && 'justify-end', className)}>
            <Button
                variant="ghost"
                size="sm"
                className={cn(
                    'h-8 data-[state=open]:bg-accent capitalize font-semibold hover:bg-transparent',
                    isRightAligned ? '-mr-3' : '-ml-3'
                )}
                onClick={() => table.onSort(sortKey)}
            >
                <span className="text-foreground">{title}</span>
                <div className="ml-1.5 flex flex-col">
                    {isActive ? (
                        table.state.direction === 'desc' ? (
                            <ChevronDown className="h-3 w-3 text-primary" />
                        ) : (
                            <ChevronUp className="h-3 w-3 text-primary" />
                        )
                    ) : (
                        <ChevronsUpDown className="h-3.5 w-3.5 text-muted-foreground/60" />
                    )}
                </div>
            </Button>
        </div>
    );
}
