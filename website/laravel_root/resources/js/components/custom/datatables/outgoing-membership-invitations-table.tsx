import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Trash2, Eye, Edit } from 'lucide-react';
import { MembershipInvitationStatusBadge, INVITATION_STATUSES } from '@/components/custom/badges/membership-invitation-status-badge';
import { DataTable } from '@/components/data-table/data-table';
import { useDataTable } from '@/components/data-table/hooks/use-data-table';
import { type MembershipInvitation, type PaginatedResponse, type Abilities } from '@/types';
import { type ColumnDef, type FilterConfig } from '@/components/data-table/types';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { ViewMembershipInvitationDialog } from '@/components/custom/dialogs/view-membership-invitation-dialog';
import { EditMembershipInvitationDialog } from '@/components/custom/dialogs/edit-membership-invitation-dialog';
import { ConfirmationDialog } from '@/components/custom/dialogs/confirmation-dialog';
import { RolesCell } from '@/components/custom/columns/roles-column';
import { PermissionsCell } from '@/components/custom/columns/permissions-column';

export interface InvitationWithAbilities extends MembershipInvitation {
    abilities: Abilities;
}

interface OutgoingMembershipInvitationsTableProps {
    invitations: PaginatedResponse<InvitationWithAbilities>;
    availableRoles: string[];
    assignableRoles: string[];
    availablePermissions: string[];
}

export function OutgoingMembershipInvitationsTable({
    invitations,
    availableRoles,
    assignableRoles,
    availablePermissions,
}: OutgoingMembershipInvitationsTableProps) {
    // 1. Action State
    const [viewingInvitation, setViewingInvitation] = useState<InvitationWithAbilities | null>(null);
    const [isViewDialogOpen, setIsViewDialogOpen] = useState(false);

    const [editingInvitation, setEditingInvitation] = useState<InvitationWithAbilities | null>(null);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);

    const [invitationToRevoke, setInvitationToRevoke] = useState<InvitationWithAbilities | null>(null);
    const [isRevokeDialogOpen, setIsRevokeDialogOpen] = useState(false);

    // 2. Handlers
    const handleRevoke = () => {
        if (!invitationToRevoke) return;
        router.post(`/tenant/invitations/${invitationToRevoke.uuid}/revoke`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                setIsRevokeDialogOpen(false);
                setInvitationToRevoke(null);
            },
        });
    };

    // 3. Table Engine Hook
    const table = useDataTable({
        data: invitations.data,
        meta: invitations,
        namespace: 'inv',
        initialFilters: {
            status: ['pending'],
        },
    });

    // 4. Columns definition
    const columns: ColumnDef<InvitationWithAbilities>[] = [
        {
            accessorKey: 'email',
            header: 'Email',
            sortable: true,
            className: 'max-w-[250px] truncate font-medium',
            headerClassName: 'max-w-[250px] truncate',
        },
        {
            accessorKey: 'roles',
            header: 'Role(s)',
            sortable: true,
            cell: ({ row }) => <RolesCell roles={row.roles} variant="outline" />,
        },
        {
            accessorKey: 'permissions',
            header: 'Permissions',
            sortable: true,
            className: 'hidden lg:table-cell',
            headerClassName: 'hidden lg:table-cell',
            cell: ({ row }) => <PermissionsCell permissions={row.permissions} />,
        },
        {
            accessorKey: 'status',
            header: 'Status',
            sortable: true,
            cell: ({ value }) => <MembershipInvitationStatusBadge status={value as any} />
        },
        {
            accessorKey: 'expires_at',
            header: 'Expires',
            sortable: true,
            align: 'right',
            className: 'text-[10px] leading-tight text-muted-foreground',
            cell: ({ value }) => value ? (
                <div className="flex flex-col">
                    {value.split(' ').map((part: string, i: number) => (
                        <span key={i}>{part}</span>
                    ))}
                </div>
            ) : 'Never'
        },
        {
            id: 'actions',
            header: 'Actions',
            align: 'right',
            cell: ({ row }) => (
                <DropdownMenu>
                    <DropdownMenuTrigger
                        render={
                            <Button variant="ghost" className="h-8 w-8 p-0" />
                        }
                    >
                        <span className="sr-only">Open menu</span>
                        <MoreHorizontal className="h-4 w-4" />
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={() => { setViewingInvitation(row); setIsViewDialogOpen(true); }}>
                            <Eye className="mr-2 h-4 w-4" />
                            <span>View</span>
                        </DropdownMenuItem>
                        {row.status === 'pending' && (
                            <>
                                <TooltipProvider>
                                    {row.abilities?.update ? (
                                        <DropdownMenuItem onClick={() => { setEditingInvitation(row); setIsEditDialogOpen(true); }}>
                                            <Edit className="mr-2 h-4 w-4" />
                                            <span>Edit</span>
                                        </DropdownMenuItem>
                                    ) : (
                                        <Tooltip>
                                            <TooltipTrigger 
                                                render={
                                                    <div className="w-full cursor-not-allowed">
                                                        <DropdownMenuItem disabled>
                                                            <Edit className="mr-2 h-4 w-4" />
                                                            <span>Edit</span>
                                                        </DropdownMenuItem>
                                                    </div>
                                                }
                                            />
                                            <TooltipContent side="left">
                                                You don't have permission to edit this invitation
                                            </TooltipContent>
                                        </Tooltip>
                                    )}

                                    {row.abilities?.revoke ? (
                                        <DropdownMenuItem 
                                            variant="destructive"
                                            onClick={() => { setInvitationToRevoke(row); setIsRevokeDialogOpen(true); }}
                                        >
                                            <Trash2 className="mr-2 h-4 w-4" />
                                            <span>Revoke</span>
                                        </DropdownMenuItem>
                                    ) : (
                                        <Tooltip>
                                            <TooltipTrigger 
                                                render={
                                                    <div className="w-full cursor-not-allowed">
                                                        <DropdownMenuItem disabled variant="destructive">
                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                            <span>Revoke</span>
                                                        </DropdownMenuItem>
                                                    </div>
                                                }
                                            />
                                            <TooltipContent side="left">
                                                You don't have permission to revoke this invitation
                                            </TooltipContent>
                                        </Tooltip>
                                    )}
                                </TooltipProvider>
                            </>
                        )}
                    </DropdownMenuContent>
                </DropdownMenu>
            )
        }
    ];

    // 5. Filters Definition (Co-located)
    const filters: FilterConfig[] = [
        {
            key: 'search',
            label: 'Email',
            type: 'text',
            placeholder: 'Filter emails...',
        },
        {
            key: 'status',
            label: 'Status',
            type: 'faceted',
            options: Object.entries(INVITATION_STATUSES).map(([value, meta]) => ({
                label: meta.label,
                value,
                component: ({ value, isSelected }: any) => (
                    <MembershipInvitationStatusBadge status={value as any} className={isSelected ? 'font-medium' : ''} />
                ),
            })),
        },
        {
            key: 'expires_at',
            label: 'Expires',
            type: 'timeframe',
        },
    ];

    return (
        <>
            <DataTable 
                table={table}
                columns={columns}
                filters={filters}
            />

            <ViewMembershipInvitationDialog
                invitation={viewingInvitation}
                open={isViewDialogOpen}
                onOpenChange={setIsViewDialogOpen}
            />

            <EditMembershipInvitationDialog
                invitation={editingInvitation}
                open={isEditDialogOpen}
                onOpenChange={setIsEditDialogOpen}
                availableRoles={availableRoles}
                assignableRoles={assignableRoles}
                availablePermissions={availablePermissions}
            />

            <ConfirmationDialog
                open={isRevokeDialogOpen}
                onOpenChange={setIsRevokeDialogOpen}
                title="Revoke Invitation"
                description={`Are you sure you want to revoke the invitation for ${invitationToRevoke?.email}?`}
                onConfirm={handleRevoke}
                variant="destructive"
                confirmText="Revoke"
            />
        </>
    );
}

