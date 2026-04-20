import { Users } from 'lucide-react';
import { Avatar, AvatarFallback, AvatarImage, AvatarGroup, AvatarGroupCount } from '@/components/ui/avatar';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { type User } from '@/types';
import { Field, FieldLabel, FieldContent } from '@/components/ui/field';

interface TenantMembersFieldProps {
    users?: User[];
    totalCount: number;
    showUsers: boolean;
}

export function TenantMembersField({ users, totalCount, showUsers }: TenantMembersFieldProps) {
    const displayedUsers = users ?? [];

    return (
        <Field>
            <FieldLabel className="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70">
                Members
            </FieldLabel>
            <FieldContent className="flex-row items-center gap-2 text-foreground text-sm font-semibold">
                {showUsers && displayedUsers.length > 0 ? (
                    <AvatarGroup className="mr-1">
                        {displayedUsers.slice(0, 3).map((user) => (
                            <div key={user.uuid}>
                                <Tooltip>
                                    <TooltipTrigger render={(props) => (
                                        <Avatar {...props} size="sm" className="cursor-help">
                                            <AvatarImage src={user.avatar} />
                                            <AvatarFallback>{user.name.substring(0, 2).toUpperCase()}</AvatarFallback>
                                        </Avatar>
                                    )} />
                                    <TooltipContent className="flex flex-col gap-0.5 px-3 py-1.5 side-top">
                                        <p className="text-xs font-bold leading-none">{user.name}</p>
                                        {user.tenant_roles && user.tenant_roles.length > 0 && (
                                            <p className="text-[10px] text-muted-foreground font-medium capitalize leading-none">
                                                {user.tenant_roles.join(', ').replace('_', ' ')}
                                            </p>
                                        )}
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                        ))}
                        {totalCount > displayedUsers.slice(0, 3).length && (
                            <AvatarGroupCount key="overflow-count" className="size-6 text-[10px]">
                                +{totalCount - displayedUsers.slice(0, 3).length}
                            </AvatarGroupCount>
                        )}
                    </AvatarGroup>
                ) : (
                    <>
                        <Users className="size-4 shrink-0 text-primary/60" />
                        <span>{totalCount}</span>
                    </>
                )}
            </FieldContent>
        </Field>
    );
}
