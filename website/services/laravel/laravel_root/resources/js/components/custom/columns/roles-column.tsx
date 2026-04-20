import { Badge } from '@/components/ui/badge';

/**
 * Reusable cell renderer for role lists.
 * Shows up to 2 role badges, + overflow badge, empty state.
 * Badge variant differs by context — pass 'secondary' for members, 'outline' for invitations.
 */
export function RolesCell({
    roles,
    variant = 'secondary',
}: {
    roles: string[];
    variant?: 'secondary' | 'outline';
}) {
    if (roles.length === 0) {
        return <span className="text-xs text-muted-foreground italic text-nowrap">No roles</span>;
    }

    return (
        <div className="flex flex-wrap gap-1 max-w-[200px]">
            {roles.slice(0, 2).map((role) => (
                <Badge key={role} variant={variant} className="capitalize">
                    {role}
                </Badge>
            ))}
            {roles.length > 2 && (
                <Badge variant="outline" className="text-muted-foreground">
                    +{roles.length - 2} more
                </Badge>
            )}
        </div>
    );
}
