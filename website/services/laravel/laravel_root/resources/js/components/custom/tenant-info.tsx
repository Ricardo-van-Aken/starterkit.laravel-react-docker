import { SquareAvatar, SquareAvatarIconFallback } from '@/components/custom/avatar';
import { type Tenant } from '@/types';
import { cn } from '@/lib/utils';

interface TenantInfoProps {
    tenant: Tenant;
    roles?: string[];
    className?: string;
}

export function TenantInfo({
    tenant,
    roles,
    className,
}: TenantInfoProps) {
    return (
        <div className={cn("flex items-center gap-3", className)}>
            <SquareAvatar className="overflow-hidden">
                <SquareAvatarIconFallback seed={tenant.uuid} iconSize="size-5" />
            </SquareAvatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium text-foreground">{tenant.name}</span>
                {roles && roles.length > 0 && (
                    <span className="truncate text-xs text-muted-foreground font-normal">
                        {roles.join(', ')}
                    </span>
                )}
            </div>
        </div>
    );
}
