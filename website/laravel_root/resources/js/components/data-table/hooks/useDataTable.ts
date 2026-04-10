import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { toNamespaceParams } from '../lib/queryParams';

export interface DataTableState {
    search: string;
    sort: string;
    direction: 'asc' | 'desc';
    page: number;
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
        
        const extractedFilters: Record<string, any> = { ...initialFilters };
        
        // Extract filters, handling potential arrays (Inertia serializes arrays as key[])
        const seenActualKeys = new Set<string>();
        for (const key of searchParams.keys()) {
            if (key.startsWith(prefix)) {
                const actualKey = key.replace(prefix, '').replace('[]', '');
                if (!['search', 'sort', 'direction', 'page'].includes(actualKey)) {
                    seenActualKeys.add(actualKey);
                }
            }
        }

        seenActualKeys.forEach(key => {
            // Check both namespaced and non-namespaced keys, with and without brackets
            const fullKey = namespace ? `${namespace}_${key}` : key;
            let values = searchParams.getAll(fullKey);
            if (values.length === 0) {
                values = searchParams.getAll(`${fullKey}[]`);
            }
            
            if (values.length > 0) {
                extractedFilters[key] = values.length > 1 ? values : values[0];
            }
        });

        return {
            search: (searchParams.get(`${prefix}search`) || '') as string,
            sort: (searchParams.get(`${prefix}sort`) || '') as string,
            direction: (searchParams.get(`${prefix}direction`) === 'asc' ? 'asc' : 'desc') as 'asc' | 'desc',
            page: parseInt(searchParams.get(`${prefix}page`) || '1'),
            filters: extractedFilters,
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
        const queryParams = Object.fromEntries(new URLSearchParams(window.location.search));
        
        const tableParams = toNamespaceParams({
            search: newState.search,
            sort: newState.sort,
            direction: newState.direction,
            page: newState.page,
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
        const newState: DataTableState = {
            ...state,
            filters: { ...state.filters, [key]: value },
            page: 1,
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

    const isFiltered: boolean = state.search !== '' || JSON.stringify(state.filters) !== JSON.stringify(initialFilters);

    return {
        data,
        meta,
        state,
        localSearch,
        isFiltered,
        onSearch: setLocalSearch,
        onSort,
        onFilterChange,
        onClearFilters,
    };
}
