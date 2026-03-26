import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type Tenant } from '@/types';
import { Building2, Users, ArrowRight } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';

interface TenantCardProps {
    tenant: Tenant;
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}

export function TenantCard({ tenant, size = 'md', className }: TenantCardProps) {
    const sizeClasses = {
        sm: 'p-3 gap-2',
        md: 'p-5 gap-4',
        lg: 'p-8 gap-6',
    };

    const iconSizes = {
        sm: 'h-4 w-4',
        md: 'h-5 w-5',
        lg: 'h-6 w-6',
    };

    const titleSizes = {
        sm: 'text-sm',
        md: 'text-lg',
        lg: 'text-2xl',
    };

    return (
        <Card className={cn('group relative overflow-hidden transition-all hover:shadow-md outline-none focus-visible:ring-2 focus-visible:ring-ring', className)}>
            <CardHeader className={cn('pb-2', size === 'sm' && 'p-3', size === 'lg' && 'p-8 pb-4')}>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <div className={cn("flex items-center justify-center rounded-lg bg-primary/10 text-primary", iconSizes[size])}>
                            <Building2 className={cn(iconSizes[size], "p-1")} />
                        </div>
                        <CardTitle className={cn('font-bold tracking-tight', titleSizes[size])}>
                            {tenant.name}
                        </CardTitle>
                    </div>
                    {size !== 'sm' && (
                        <ArrowRight className="h-4 w-4 text-muted-foreground opacity-0 transition-all group-hover:translate-x-1 group-hover:opacity-100" />
                    )}
                </div>
            </CardHeader>
            <CardContent className={cn('flex items-center gap-4', size === 'sm' && 'p-3 pt-0', size === 'lg' && 'p-8 pt-0')}>
                <div className="flex items-center gap-1.5 text-muted-foreground text-sm">
                    <Users className="h-4 w-4" />
                    <span>{tenant.users_count ?? 0}</span>
                </div>
                <div className="flex items-center gap-1.5 text-muted-foreground text-sm">
                    <Building2 className="h-4 w-4" />
                    <span>{tenant.organization_units_count ?? 0}</span>
                </div>
            </CardContent>
        </Card>
    );
}
