/**
 * Utility to handle table parameter naming with optional namespaces.
 * This prevents multiple tables on the same page from clashing in the URL.
 */

export function getNamespaceKey(key: string, namespace?: string): string {
    return namespace ? `${namespace}_${key}` : key;
}

/**
 * Filters and transforms an object into namespaced parameters.
 */
export function toNamespaceParams(
    params: Record<string, any>,
    namespace?: string
): Record<string, any> {
    const namespaced: Record<string, any> = {};
    
    Object.entries(params).forEach(([key, value]) => {
        namespaced[getNamespaceKey(key, namespace)] = value;
    });
    
    return namespaced;
}

/**
 * Extracts namespaced parameters from a standard object (like current URL params).
 */
export function fromNamespaceParams(
    allParams: Record<string, any>,
    namespace?: string
): Record<string, any> {
    const extracted: Record<string, any> = {};
    const prefix = namespace ? `${namespace}_` : '';
    
    Object.entries(allParams).forEach(([key, value]) => {
        if (namespace) {
            if (key.startsWith(prefix)) {
                extracted[key.replace(prefix, '')] = value;
            }
        } else {
            // If no namespace, we might want to avoid picking up other tables' namespaced params
            // but for simplicity, we just return everything that doesn't look like it belongs to another namespace?
            // Actually, we'll just check if it DOESN'T have an underscore if you really want to be strict.
            // But usually, common params like 'search' or 'page' are fine.
            extracted[key] = value;
        }
    });
    
    return extracted;
}
