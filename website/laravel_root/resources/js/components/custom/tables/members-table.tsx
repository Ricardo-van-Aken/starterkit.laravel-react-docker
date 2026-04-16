import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { MoreHorizontal, Edit, Trash2, Eye } from 'lucide-react';
import { UserInfo } from '@/components/starterkit/user-info';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

import { DataTable } from '@/components/data-table/data-table';
import { useDataTable } from '@/components/data-table/hooks/use-data-table';
import { type ColumnDef, type FilterConfig } from '@/components/data-table/types';
import { type PaginatedResponse, type Abilities, type Member as BaseMember } from '@/types';
import { EditMemberDialog } from '@/components/custom/dialogs/edit-member-dialog';
import { ConfirmationDialog } from '@/components/custom/dialogs/confirmation-dialog';
import { ViewMemberDialog } from '@/components/custom/dialogs/view-member-dialog';
import tenant from '@/routes/tenant';

export interface MemberWithAbilities extends BaseMember {
    abilities: Abilities;
}

interface MembersTableProps {
    members: PaginatedResponse<MemberWithAbilities>;
    availableRoles: string[];
    manageableRoles: string[];
    availablePermissions: string[];
}

export function MembersTable({ 
    members, 
    availableRoles, 
    manageableRoles,
    availablePermissions, 
}: MembersTableProps) {
    const [viewingMember, setViewingMember] = useState<MemberWithAbilities | null>(null);
    const [isViewDialogOpen, setIsViewDialogOpen] = useState(false);

    const [editingMember, setEditingMember] = useState<MemberWithAbilities | null>(null);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);

    const [memberToDelete, setMemberToDelete] = useState<MemberWithAbilities | null>(null);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);

    const handleView = (member: MemberWithAbilities) => {
        setViewingMember(member);
        setIsViewDialogOpen(true);
    };

    const handleEdit = (member: MemberWithAbilities) => {
        setEditingMember(member);
        setIsEditDialogOpen(true);
    };

    const confirmDelete = (member: MemberWithAbilities) => {
        setMemberToDelete(member);
        setIsDeleteDialogOpen(true);
    };

    const handleDelete = () => {
        if (!memberToDelete) return;
        
        router.delete((tenant.members as any).destroy.url({ user: memberToDelete.uuid }), {
            preserveScroll: true,
            onSuccess: () => {
                setIsDeleteDialogOpen(false);
                setMemberToDelete(null);
            },
        });
    };

    const table = useDataTable({
        data: members.data,
        meta: members,
        namespace: 'mem',
        initialFilters: {
            roles: [],
        },
    });

    const columns: ColumnDef<MemberWithAbilities>[] = [
        {
            accessorKey: 'name',
            header: 'Member',
            sortable: true,
            className: 'max-w-[250px] truncate font-medium',
            headerClassName: 'max-w-[250px] truncate',
            cell: ({ row }) => (
                <div className="flex items-center gap-3">
                    <UserInfo user={row} showEmail />
                </div>
            )
        },
        {
            accessorKey: 'roles',
            header: 'Role(s)',
            sortable: true,
            cell: ({ row }) => (
                <div className="flex flex-wrap gap-1 max-w-[200px]">
                    {row.roles.slice(0, 2).map((role) => (
                        <Badge key={role} variant="secondary" className="capitalize">
                            {role}
                        </Badge>
                    ))}
                    {row.roles.length > 2 && (
                        <Badge variant="outline" className="text-muted-foreground">
                            +{row.roles.length - 2} more
                        </Badge>
                    )}
                    {row.roles.length === 0 && (
                        <span className="text-xs text-muted-foreground italic text-nowrap">No roles</span>
                    )}
                </div>
            )
        },
        {
            accessorKey: 'permissions',
            header: 'Permissions',
            sortable: true,
            className: 'hidden lg:table-cell',
            headerClassName: 'hidden lg:table-cell',
            cell: ({ row }) => (
                <div className="flex flex-wrap gap-1 max-w-[200px]">
                    {row.permissions.slice(0, 3).map((permission) => (
                        <span key={permission} className="text-[10px] text-muted-foreground bg-secondary/50 px-1 rounded">
                            {permission}
                        </span>
                    ))}
                    {row.permissions.length > 3 && (
                        <span className="text-[10px] text-muted-foreground">
                            +{row.permissions.length - 3} more
                        </span>
                    )}
                </div>
            )
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
                        <DropdownMenuItem onClick={() => handleView(row)}>
                            <Eye className="mr-2 h-4 w-4" />
                            <span>View</span>
                        </DropdownMenuItem>
                        <TooltipProvider>
                            {row.abilities?.update ? (
                                <DropdownMenuItem onClick={() => handleEdit(row)}>
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
                                        You don't have permission to edit this member
                                    </TooltipContent>
                                </Tooltip>
                            )}

                            {row.abilities?.delete ? (
                                <DropdownMenuItem 
                                    variant="destructive"
                                    onClick={() => confirmDelete(row)}
                                >
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    <span>Delete</span>
                                </DropdownMenuItem>
                            ) : (
                                <Tooltip>
                                    <TooltipTrigger 
                                        render={
                                            <div className="w-full cursor-not-allowed">
                                                <DropdownMenuItem disabled variant="destructive">
                                                    <Trash2 className="mr-2 h-4 w-4" />
                                                    <span>Delete</span>
                                                </DropdownMenuItem>
                                            </div>
                                        }
                                    />
                                    <TooltipContent side="left">
                                        You don't have permission to delete this member
                                    </TooltipContent>
                                </Tooltip>
                            )}
                        </TooltipProvider>
                    </DropdownMenuContent>
                </DropdownMenu>
            )
        }
    ];

    const filters: FilterConfig[] = [
        {
            key: 'search',
            label: 'Name or Email',
            type: 'text',
            placeholder: 'Filter members...',
        },
        {
            key: 'roles',
            label: 'Role',
            type: 'faceted',
            options: availableRoles.map(role => ({
                label: role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                value: role,
            })),
        },
    ];

    return (
        <>
            <DataTable 
                table={table}
                columns={columns}
                filters={filters}
            />

            <ViewMemberDialog
                member={viewingMember}
                open={isViewDialogOpen}
                onOpenChange={setIsViewDialogOpen}
            />

            <EditMemberDialog
                member={editingMember}
                open={isEditDialogOpen}
                onOpenChange={setIsEditDialogOpen}
                availableRoles={availableRoles}
                manageableRoles={manageableRoles}
                availablePermissions={availablePermissions}
            />

            <ConfirmationDialog
                open={isDeleteDialogOpen}
                onOpenChange={setIsDeleteDialogOpen}
                title="Remove Member"
                description={`Are you sure you want to remove ${memberToDelete?.name} from this tenant?`}
                onConfirm={handleDelete}
                variant="destructive"
            />
        </>
    );
}
