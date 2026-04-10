import * as React from 'react';
import { CalendarDays, Clock, Hourglass, InfinityIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface DataTableTimeframeFilterProps {
    title?: string;
    value?: string;
    onSelect: (value: string | null) => void;
}

const TIMEFRAME_OPTIONS = [
    { label: 'All', value: 'all', icon: InfinityIcon },
    { label: 'Next 24 Hours', value: '24h', icon: Hourglass },
    { label: 'Next 7 Days', value: '7d', icon: CalendarDays },
    { label: 'Next 30 Days', value: '30d', icon: Clock },
    { label: 'Expired', value: 'expired', icon: Clock },
];

export function DataTableTimeframeFilter({
    title,
    value = 'all',
    onSelect,
}: DataTableTimeframeFilterProps) {
    return (
        <Select value={value} onValueChange={onSelect}>
            <SelectTrigger className="h-8 w-fit min-w-[130px] border-dashed text-xs">
                <CalendarDays className="mr-2 h-4 w-4 text-muted-foreground" />
                <SelectValue placeholder={title} />
            </SelectTrigger>
            <SelectContent>
                {TIMEFRAME_OPTIONS.map((option) => (
                    <SelectItem key={option.value} value={option.value} className="text-xs">
                        <div className="flex items-center">
                            <option.icon className="mr-2 h-3.5 w-3.5 text-muted-foreground" />
                            {option.label}
                        </div>
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
