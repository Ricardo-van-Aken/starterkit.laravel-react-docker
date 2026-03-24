import { Avatar, AvatarIconFallback } from '@/components/custom/avatar';
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
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarIconFallback seed={tenant.uuid} variant="square" iconSize="size-5" />
            </Avatar>
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
