import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TenantInfo } from '@/components/custom/tenant-info';
import { router } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { MembershipInvitationStatusBadge } from '@/components/custom/badges/membership-invitation-status-badge';

import { type Tenant } from '@/types';

export interface IncomingMembershipInvitation {
    uuid: string;
    tenant: Tenant;
    roles: string[];
    permissions: string[];
    status: string;
    expires_at: string | null;
}

interface IncomingMembershipInvitationsTableProps {
    invitations: IncomingMembershipInvitation[];
}


export function IncomingMembershipInvitationsTable({ invitations }: IncomingMembershipInvitationsTableProps) {
    const handleAccept = (uuid: string) => {
        router.post(`/tenant-invitations/${uuid}/accept`);
    };

    const handleDecline = (uuid: string) => {
        if (confirm('Are you sure you want to decline this invitation?')) {
            router.post(`/tenant-invitations/${uuid}/decline`);
        }
    };

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead className="min-w-[200px]">Organization</TableHead>
                    <TableHead>Role(s)</TableHead>
                    <TableHead className="hidden lg:table-cell">Permissions</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right whitespace-nowrap">Expires</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {invitations.map((invitation) => (
                    <TableRow key={invitation.uuid}>
                        <TableCell className="font-medium py-3">
                            <div className="flex items-center gap-3">
                                <TenantInfo tenant={invitation.tenant} roles={[]} />
                            </div>
                        </TableCell>
                        <TableCell>
                            <div className="flex flex-wrap gap-1 max-w-[200px]">
                                {invitation.roles?.slice(0, 2).map((role) => (
                                    <Badge key={role} variant="secondary" className="capitalize">{role}</Badge>
                                ))}
                                {(invitation.roles?.length ?? 0) > 2 && (
                                    <Badge variant="outline" className="text-muted-foreground">
                                        +{(invitation.roles?.length ?? 0) - 2} more
                                    </Badge>
                                )}
                                {(invitation.roles?.length ?? 0) === 0 && (
                                    <span className="text-xs text-muted-foreground italic text-nowrap">No roles</span>
                                )}
                            </div>
                        </TableCell>
                        <TableCell className="hidden lg:table-cell">
                            <div className="flex flex-wrap gap-1 max-w-[200px]">
                                {invitation.permissions?.slice(0, 3).map((permission) => (
                                    <span key={permission} className="text-[10px] text-muted-foreground bg-secondary/50 px-1 rounded">
                                        {permission}
                                    </span>
                                ))}
                                {(invitation.permissions?.length ?? 0) > 3 && (
                                    <span className="text-[10px] text-muted-foreground">
                                        +{(invitation.permissions?.length ?? 0) - 3} more
                                    </span>
                                )}
                                {(invitation.permissions?.length ?? 0) === 0 && (
                                    <span className="text-xs text-muted-foreground italic text-nowrap">No permissions</span>
                                )}
                            </div>
                        </TableCell>
                        <TableCell>
                            <MembershipInvitationStatusBadge status={invitation.status as any} />
                        </TableCell>
                        <TableCell className="text-right text-[10px] leading-tight text-muted-foreground">
                            {invitation.expires_at ? (
                                <div className="flex flex-col">
                                    {invitation.expires_at.split(' ').map((part, i) => (
                                        <span key={i}>{part}</span>
                                    ))}
                                </div>
                            ) : (
                                'Never'
                            )}
                        </TableCell>
                        <TableCell className="text-right">
                            <div className="flex justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
                                    onClick={() => handleAccept(invitation.uuid)}
                                    title="Accept Invitation"
                                >
                                    <Check className="h-4 w-4" />
                                    <span className="sr-only">Accept</span>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    onClick={() => handleDecline(invitation.uuid)}
                                    title="Decline Invitation"
                                >
                                    <X className="h-4 w-4" />
                                    <span className="sr-only">Decline</span>
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
                {invitations.length === 0 && (
                    <TableRow>
                        <TableCell colSpan={5} className="h-24 text-center text-muted-foreground font-medium">
                            No pending invitations.
                        </TableCell>
                    </TableRow>
                )}
            </TableBody>
        </Table>
    );
}
