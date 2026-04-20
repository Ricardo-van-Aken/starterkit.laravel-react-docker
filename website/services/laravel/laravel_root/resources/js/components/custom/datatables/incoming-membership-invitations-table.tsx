import { Button } from '@/components/ui/button';
import { TenantInfo } from '@/components/custom/tenant-info';
import { router } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { MembershipInvitationStatusBadge } from '@/components/custom/badges/membership-invitation-status-badge';
import { DataTable } from '@/components/data-table/data-table';
import { useDataTable } from '@/components/data-table/hooks/use-data-table';
import { type ColumnDef } from '@/components/data-table/types';
import { cn } from '@/lib/utils';
import { RolesCell } from '@/components/custom/columns/roles-column';
import { PermissionsCell } from '@/components/custom/columns/permissions-column';

import { type Tenant, type PaginatedResponse } from '@/types';

export interface IncomingMembershipInvitation {
    uuid: string;
    tenant: Tenant;
    roles: string[];
    permissions: string[];
    status: string;
    expires_at: string | null;
}

interface IncomingMembershipInvitationsTableProps {
    invitations: PaginatedResponse<IncomingMembershipInvitation>;
}

export function IncomingMembershipInvitationsTable({ invitations }: IncomingMembershipInvitationsTableProps) {
    const { onPageSizeChange: _, ...table } = useDataTable({
        data: invitations.data,
        meta: invitations.links,
    });

    const handleAccept = (uuid: string) => {
        router.post(`/tenant-invitations/${uuid}/accept`);
    };

    const handleDecline = (uuid: string) => {
        if (confirm('Are you sure you want to decline this invitation?')) {
            router.post(`/tenant-invitations/${uuid}/decline`);
        }
    };

    const columns: ColumnDef<IncomingMembershipInvitation>[] = [
        {
            id: 'organization',
            header: 'Organization',
            className: 'min-w-[200px] py-3',
            cell: ({ row }) => <TenantInfo tenant={row.tenant} roles={[]} />,
        },
        {
            id: 'roles',
            header: 'Role(s)',
            cell: ({ row }) => <RolesCell roles={row.roles ?? []} />,
        },
        {
            id: 'permissions',
            header: 'Permissions',
            className: 'hidden lg:table-cell',
            headerClassName: 'hidden lg:table-cell',
            cell: ({ row }) => <PermissionsCell permissions={row.permissions ?? []} />,
        },
        {
            id: 'status',
            header: 'Status',
            cell: ({ row }) => <MembershipInvitationStatusBadge status={row.status as any} />,
        },
        {
            accessorKey: 'expires_at',
            header: 'Expires',
            align: 'right',
            className: 'text-[10px] leading-tight text-muted-foreground',
            cell: ({ value }) => (
                value ? (
                    <div className="flex flex-col">
                        {(value as string).split(' ').map((part, i) => (
                            <span key={i}>{part}</span>
                        ))}
                    </div>
                ) : 'Never'
            ),
        },
        {
            id: 'actions',
            header: 'Actions',
            align: 'right',
            cell: ({ row }) => (
                <div className="flex justify-end gap-1.5">
                    <Button
                        variant="ghost"
                        size="icon"
                        className={cn(
                            "h-8 w-8 transition-all duration-200",
                            "text-emerald-600 hover:text-white hover:bg-emerald-600 hover:scale-110 hover:shadow-lg hover:shadow-emerald-500/20"
                        )}
                        onClick={() => handleAccept(row.uuid)}
                        title="Accept Invitation"
                    >
                        <Check className="h-4 w-4" />
                        <span className="sr-only">Accept</span>
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        className={cn(
                            "h-8 w-8 transition-all duration-200",
                            "text-destructive hover:text-white hover:bg-destructive hover:scale-110 hover:shadow-lg hover:shadow-destructive/20"
                        )}
                        onClick={() => handleDecline(row.uuid)}
                        title="Decline Invitation"
                    >
                        <X className="h-4 w-4" />
                        <span className="sr-only">Decline</span>
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <DataTable
            table={table}
            columns={columns}
        />
    );
}
