import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toNamespaceParams } from '../lib/queryParams';

export interface DataTableState {
    search: string;
    sort: string;
    direction: 'asc' | 'desc';
    page: number;
    pageSize: number;
    filters: Record<string, any>;
}

export interface UseDataTableProps {
    data: any[];
    meta: any;
    namespace?: string;
    initialFilters?: Record<string, any>;
}

export function useDataTable({
    data,
    meta,
    namespace,
    initialFilters = {},
}: UseDataTableProps) {
    const getInitialState = (): DataTableState => {
        const searchParams = new URLSearchParams(window.location.search);
        const prefix = namespace ? `${namespace}_` : '';
        const filters: Record<string, any> = { ...initialFilters };
        
        // Extract filters for this table from the url
        const urlKeys = new Set<string>();
        for (const [rawKey, value] of searchParams.entries()) {
            // Check if parameter belongs to the current table
            if (rawKey.startsWith(prefix)) {
                const key = rawKey.replace(prefix, '').replace(/\[.*\]$/, '');
                
                // Skip core parameters; everything else is a filter
                if (!['page', 'per_page', 'search', 'sort', 'dir'].includes(key)) {
                    if (! urlKeys.has(key)) {
                        // First time we encounter this specific filter key, we overwrite the default 'initialFilter'
                        filters[key] = [value];
                        urlKeys.add(key);
                    } else {
                        // We have already encountered this filter key, so we append the current value to the existing array
                        filters[key].push(value);
                    }
                }
            }
        }

        return {
            search: (searchParams.get(`${prefix}search`) || '') as string,
            sort: (searchParams.get(`${prefix}sort`) || '') as string,
            direction: (searchParams.get(`${prefix}dir`) === 'asc' ? 'asc' : 'desc') as 'asc' | 'desc',
            page: parseInt(searchParams.get(`${prefix}page`) || '1'),
            pageSize: parseInt(searchParams.get(`${prefix}per_page`) || '10'),
            filters: filters,
        };
    };

    const [state, setState] = useState<DataTableState>(getInitialState);
    const [localSearch, setLocalSearch] = useState<string>(state.search);
    const isFirstRender = useRef(true);

    // Sync local state if props change (e.g. on external navigation or reset)
    useEffect(() => {
        const newState: DataTableState = getInitialState();
        setState(newState);
        setLocalSearch(newState.search);
    }, [window.location.search]);

    const updateUrl = useCallback((newState: DataTableState) => {
        const searchParams = new URLSearchParams(window.location.search);
        const prefix = namespace ? `${namespace}_` : '';
        
        // Remove all current namespaced params to prevent duplicates
        const queryParams: Record<string, any> = {};
        for (const [key, value] of searchParams.entries()) {
            if (!key.startsWith(prefix)) {
                queryParams[key] = value;
            }
        }
        
        const tableParams = toNamespaceParams({
            search: newState.search,
            sort: newState.sort,
            dir: newState.direction,
            page: newState.page,
            per_page: newState.pageSize,
            ...newState.filters,
        }, namespace);

        router.get(window.location.pathname, { ...queryParams, ...tableParams }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, [namespace]);

    // Handle sort
    const onSort = (sortKey: string) => {
        const direction: 'asc' | 'desc' = state.sort === sortKey && state.direction === 'asc' ? 'desc' : 'asc';
        const newState: DataTableState = { 
            ...state, 
            sort: sortKey, 
            direction: direction, 
            page: 1 
        };
        setState(newState);
        updateUrl(newState);
    };

    // Handle filter change
    const onFilterChange = (key: string, value: any) => {
        // If a multi-select filter is cleared, use ['all'] as a filter to distinguish from the 
        // initial (empty) state which might have default filters.
        const actualValue = Array.isArray(value) && value.length === 0 ? ['all'] : value;

        const newState: DataTableState = {
            ...state,
            filters: { ...state.filters, [key]: actualValue },
            page: 1,
        };
        setState(newState);
        updateUrl(newState);
    };

    // Handle page size change
    const onPageSizeChange = (newPageSize: number) => {
        const oldTopElementIndex = (state.page - 1) * state.pageSize;
        const newPage = Math.floor(oldTopElementIndex / newPageSize) + 1;

        const newState: DataTableState = {
            ...state,
            pageSize: newPageSize,
            page: newPage,
        };
        setState(newState);
        updateUrl(newState);
    };

    // Handle search with debounce
    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        if (localSearch === state.search) return;

        const timer = setTimeout(() => {
            const newState: DataTableState = { ...state, search: localSearch, page: 1 };
            setState(newState);
            updateUrl(newState);
        }, 300);

        return () => clearTimeout(timer);
    }, [localSearch]);

    const onClearFilters = () => {
        const newState: DataTableState = {
            ...state,
            search: '',
            filters: initialFilters,
            direction: 'desc',
            page: 1,
        };
        setLocalSearch('');
        setState(newState);
        updateUrl(newState);
    };

    const isFiltered: boolean = state.search !== '' || JSON.stringify(
        Object.fromEntries(
            Object.entries(state.filters).map(([k, v]) => [
                k,
                Array.isArray(v) ? v.filter(i => i !== 'all') : v
            ])
        )
    ) !== JSON.stringify(initialFilters);

    return {
        data,
        meta,
        state: {
            ...state,
            // Provide a "clean" version of filters to components, removing the 'all' sentinel
            filters: Object.fromEntries(
                Object.entries(state.filters).map(([k, v]) => [
                    k,
                    Array.isArray(v) ? v.filter(i => i !== 'all') : v
                ])
            )
        },
        localSearch,
        isFiltered,
        onSearch: setLocalSearch,
        onSort,
        onFilterChange,
        onPageSizeChange,
        onClearFilters,
    };
}
