import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { type Tenant } from '@/types';
import { ArrowRight } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { SquareAvatar, SquareAvatarIconFallback } from '@/components/custom/avatar';
import { Separator } from '@/components/ui/separator';
import { Field, FieldLabel, FieldContent } from '@/components/ui/field';
import { TenantMembersField } from '@/components/custom/fields/tenant-members-field';
import { TenantOrgUnitsField } from '@/components/custom/fields/tenant-org-units-field';

import { type User, type Abilities, type TenantIndexItem } from '@/types';

interface TenantCardProps {
    tenant: TenantIndexItem;
    users?: User[];
    abilities?: Abilities;
    className?: string;
}

export function TenantCard({ tenant, users, abilities, className }: TenantCardProps) {
    return (
        <Card className={cn(
            "group relative w-full max-w-[400px] h-full overflow-hidden border-sidebar-border/70 dark:border-sidebar-border transition-all duration-300 hover:bg-muted/40 hover:border-primary/30 hover:shadow-md cursor-pointer",
            className
        )}>
                <CardHeader className="flex flex-row items-start gap-4 pb-4">
                    <div className="shrink-0">
                        <SquareAvatar className="size-12">
                            <SquareAvatarIconFallback seed={tenant.uuid} iconSize="size-6" />
                        </SquareAvatar>
                    </div>

                    <div className="flex flex-col flex-1 gap-0.5 text-left">
                        <CardTitle className="truncate text-xl transition-colors group-hover:text-primary">
                            {tenant.name}
                        </CardTitle>
                        <CardDescription className="text-xs font-medium text-muted-foreground/80">
                            Pro Plan
                        </CardDescription>
                    </div>
                    <ArrowRight className="h-5 w-5 text-muted-foreground/30 transition-all duration-300 group-hover:translate-x-1 group-hover:text-primary mt-1" />
                </CardHeader>

                <Separator />

                <CardContent className="pt-6 pb-5">
                    <div className="grid grid-cols-2 gap-4">
                        <TenantMembersField 
                            users={users} 
                            totalCount={tenant.users_count} 
                            showUsers={!!abilities?.view_members} 
                        />
                        <TenantOrgUnitsField count={tenant.organisation_units_count} />
                    </div>
                </CardContent>

                <CardFooter className="pt-0 pb-6">
                    <Field>
                        <FieldLabel className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70">User Roles</FieldLabel>
                        <FieldContent className="flex-row flex-wrap gap-2">
                            {tenant.roles && tenant.roles.length > 0 ? (
                                tenant.roles.map((role) => (
                                    <Badge key={role} variant="secondary" className="capitalize text-[10px] font-bold bg-primary/5 text-primary border-none py-0.5">
                                        {role.replace('_', ' ')}
                                    </Badge>
                                ))
                            ) : (
                                <Badge variant="outline" className="text-[10px] font-medium border-muted-foreground/20 py-0.5">
                                    Member
                                </Badge>
                            )}
                        </FieldContent>
                    </Field>
                </CardFooter>
            </Card>
    );
}
