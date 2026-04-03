import { SquareAvatar, SquareAvatarIconFallback } from '@/components/custom/avatar';
import { type Tenant } from '@/types';

interface TenantInfoProps {
    tenant: Tenant;
    roles?: string[];
}

export function TenantInfo({
    tenant,
    roles,
}: TenantInfoProps) {
    return (
        <>
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
        </>
    );
}
