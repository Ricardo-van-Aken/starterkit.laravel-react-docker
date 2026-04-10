import * as React from 'react';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
} from '@/components/ui/pagination';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface PaginationLinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface DataTablePaginationProps {
    table?: {
        meta?: {
            links?: PaginationLinkItem[];
        } | PaginationLinkItem[];
    };
    links?: PaginationLinkItem[];
    className?: string;
}

export function DataTablePagination({ table, links: manualLinks, className }: DataTablePaginationProps) {
    // 1. Resolve the links source
    // Priority: Explicit links prop -> table.meta.links -> table.meta (as array)
    const links = manualLinks || (table?.meta && (Array.isArray(table.meta) ? table.meta : table.meta.links));

    if (!links || links.length <= 3) return null;

    return (
        <Pagination className={className}>
            <PaginationContent>
                {links.map((link, i) => {
                    const isFirst = i === 0;
                    const isLast = i === links.length - 1;
                    const label = link.label;

                    if (label === '...') {
                        return (
                            <PaginationItem key={i}>
                                <PaginationEllipsis />
                            </PaginationItem>
                        );
                    }

                    // Map Laravel labels (e.g. "&laquo; Previous") to clean text/icons
                    let content: React.ReactNode = label;
                    if (isFirst) {
                        content = (
                            <>
                                <ChevronLeft className="size-4" />
                                <span className="hidden sm:inline">Previous</span>
                            </>
                        );
                    } else if (isLast) {
                        content = (
                            <>
                                <span className="hidden sm:inline">Next</span>
                                <ChevronRight className="size-4" />
                            </>
                        );
                    }

                    return (
                        <PaginationItem key={i}>
                            <Button
                                variant={link.active ? 'outline' : 'ghost'}
                                size={isFirst || isLast ? 'default' : 'icon'}
                                nativeButton={false} // Prevents Base UI accessibility warning when using Link
                                className={
                                    (isFirst ? 'pl-2.5 ' : isLast ? 'pr-2.5 ' : '') + 
                                    (!link.url ? 'pointer-events-none opacity-50' : '')
                                }
                                render={
                                    link.url ? (
                                        <Link 
                                            href={link.url} 
                                            preserveScroll 
                                            preserveState 
                                        />
                                    ) : (
                                        <span /> // Fallback for disabled buttons
                                    )
                                }
                            >
                                {content}
                            </Button>
                        </PaginationItem>
                    );
                })}
            </PaginationContent>
        </Pagination>
    );
}
