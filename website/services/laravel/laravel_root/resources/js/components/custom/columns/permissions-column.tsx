/**
 * Reusable cell renderer for permission lists.
 * Shows up to 3 small permission labels, + overflow count, empty state.
 */
export function PermissionsCell({ permissions }: { permissions: string[] }) {
    if (permissions.length === 0) {
        return <span className="text-xs text-muted-foreground italic text-nowrap">No permissions</span>;
    }

    return (
        <div className="flex flex-wrap gap-1 max-w-[200px]">
            {permissions.slice(0, 3).map((permission) => (
                <span
                    key={permission}
                    className="text-[10px] text-muted-foreground bg-secondary/50 px-1 rounded"
                >
                    {permission}
                </span>
            ))}
            {permissions.length > 3 && (
                <span className="text-[10px] text-muted-foreground">
                    +{permissions.length - 3} more
                </span>
            )}
        </div>
    );
}
