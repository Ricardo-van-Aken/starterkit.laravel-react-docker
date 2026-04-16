import * as React from 'react';
import { Check, PlusCircle } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';

interface DataTableFacetedFilterProps {
    title?: string;
    options: {
        label: string;
        value: string;
        component?: React.ComponentType<{ value: string; label: string; isSelected: boolean }>;
    }[];
    selectedValues?: string[];
    onSelect: (values: string[]) => void;
    onClear: () => void;
}

export function DataTableFacetedFilter({
    title,
    options,
    selectedValues = [],
    onSelect,
    onClear,
}: DataTableFacetedFilterProps) {
    const [searchTerm, setSearchTerm] = React.useState('');

    const selectedSet = React.useMemo(() => new Set(selectedValues), [selectedValues]);

    const handleSelect = (value: string) => {
        const next = new Set(selectedValues);
        if (next.has(value)) {
            next.delete(value);
        } else {
            next.add(value);
        }
        onSelect(Array.from(next));
    };

    const filteredOptions = options.filter((option) =>
        option.label.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <Popover>

            {/* Display selected elements as badges in the trigger */}
            <PopoverTrigger
                render={
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 border-dashed"
                    >
                        <PlusCircle className="mr-2 h-4 w-4" />
                        {title}
                        {selectedSet.size > 0 && (
                            <>
                                <Separator orientation="vertical" className="mx-2 h-4" />
                                <Badge
                                    variant="secondary"
                                    className="rounded-sm px-1 font-normal lg:hidden"
                                >
                                    {selectedSet.size}
                                </Badge>
                                <div className="hidden space-x-1 lg:flex">
                                    {selectedSet.size > 2 ? (
                                        <Badge
                                            variant="secondary"
                                            className="rounded-sm px-1 font-normal"
                                        >
                                            {selectedSet.size} selected
                                        </Badge>
                                    ) : (
                                        options
                                            .filter((option) => selectedSet.has(option.value))
                                            .map((option) => {
                                                const Component = option.component;
                                                return Component ? (
                                                    <Component 
                                                        key={option.value}
                                                        value={option.value}
                                                        label={option.label}
                                                        isSelected={true}
                                                    />
                                                ) : (
                                                    <Badge
                                                        variant="secondary"
                                                        key={option.value}
                                                        className="rounded-sm px-1 font-normal capitalize"
                                                    >
                                                        {option.label}
                                                    </Badge>
                                                );
                                            })
                                    )}
                                </div>
                            </>
                        )}
                    </Button>
                }
            />

            <PopoverContent className="w-[200px] p-0" align="start">
                <div className="flex flex-col">
                    {/* Search Functionality */}
                    <div className="p-2 border-b">
                        <Input
                            placeholder={title}
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="h-8 text-xs focus-visible:ring-0 focus-visible:ring-offset-0 border-none px-2 shadow-none"
                            autoFocus={false}
                        />
                    </div>

                    {/* Search-Filtered Options */}
                    <div className="max-h-[300px] overflow-y-auto p-1">
                        {filteredOptions.length === 0 ? (
                            <div className="py-6 text-center text-sm text-muted-foreground">
                                No results found.
                            </div>
                        ) : (
                            <div className="space-y-1">
                                {filteredOptions.map((option) => {
                                    const isSelected = selectedSet.has(option.value);
                                    const Component = option.component;

                                    return (
                                        <button
                                            key={option.value}
                                            onClick={() => handleSelect(option.value)}
                                            className={cn(
                                                "relative flex w-full cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
                                                isSelected && "bg-accent/50"
                                            )}
                                        >
                                            <div
                                                className={cn(
                                                    "mr-2 flex h-4 w-4 items-center justify-center rounded-sm border border-primary transition-colors",
                                                    isSelected
                                                        ? "bg-primary text-primary-foreground"
                                                        : "opacity-50"
                                                )}
                                            >
                                                {isSelected && <Check className="h-4 w-4" />}
                                            </div>
                                            
                                            {Component ? (
                                                <Component 
                                                    value={option.value} 
                                                    label={option.label} 
                                                    isSelected={isSelected} 
                                                />
                                            ) : (
                                                <span className={cn(
                                                    "capitalize",
                                                    isSelected && "font-medium"
                                                )}>
                                                    {option.label}
                                                </span>
                                            )}
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {/* Clear Filters */}
                    {selectedSet.size > 0 && (
                        <div className="border-t p-1">
                            <button
                                onClick={onClear}
                                className="relative flex w-full cursor-default select-none items-center justify-center rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground"
                            >
                                Clear filters
                            </button>
                        </div>
                    )}
                </div>
            </PopoverContent>
        </Popover>
    );
}
