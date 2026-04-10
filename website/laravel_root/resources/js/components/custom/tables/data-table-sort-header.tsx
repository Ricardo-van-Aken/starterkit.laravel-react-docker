import { Button } from '@/components/ui/button';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import { cn } from '@/lib/utils';

interface DataTableSortHeaderProps {
    title: string;
    sortKey: string;
    currentSort?: string;
    currentDirection?: 'asc' | 'desc';
    onSort: (key: string, direction: 'asc' | 'desc') => void;
    className?: string;
}

export function DataTableSortHeader({
    title,
    sortKey,
    currentSort,
    currentDirection,
    onSort,
    className,
}: DataTableSortHeaderProps) {
    const isActive = currentSort === sortKey;

    const toggleSort = () => {
        if (!isActive) {
            onSort(sortKey, 'asc');
        } else {
            onSort(sortKey, currentDirection === 'asc' ? 'desc' : 'asc');
        }
    };

    return (
        <div className={cn('flex items-center space-x-2', className)}>
            <Button
                variant="ghost"
                size="sm"
                className="-ml-3 h-8 data-[state=open]:bg-accent"
                onClick={toggleSort}
            >
                <span>{title}</span>
                {isActive ? (
                    currentDirection === 'asc' ? (
                        <ArrowUp className="ml-2 h-4 w-4" />
                    ) : (
                        <ArrowDown className="ml-2 h-4 w-4" />
                    )
                ) : (
                    <ArrowUpDown className="ml-2 h-4 w-4 opacity-50" />
                )}
            </Button>
        </div>
    );
}
