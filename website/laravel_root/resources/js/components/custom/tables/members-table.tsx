import { UserInfo } from '@/components/starterkit/user-info';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { EditMemberDialog } from '@/components/custom/dialogs/edit-member-dialog';
import { ConfirmationDialog } from '@/components/custom/dialogs/confirmation-dialog';
import { ViewMemberDialog } from '@/components/custom/dialogs/view-member-dialog';
import { type User, type PaginatedResponse } from '@/types';
import { MoreHorizontal, Edit, Trash2, Eye } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import tenant from '@/routes/tenant';
import { DataTablePagination } from '@/components/data-table/DataTablePagination';

export interface Member extends User {
    roles: string[];
    permissions: string[];
}

interface MembersTableProps {
    members: PaginatedResponse<Member>;
    availableRoles: string[];
    availablePermissions: string[];
}

export function MembersTable({ members, availableRoles, availablePermissions }: MembersTableProps) {
    const [viewingMember, setViewingMember] = useState<Member | null>(null);
    const [isViewDialogOpen, setIsViewDialogOpen] = useState(false);

    const [editingMember, setEditingMember] = useState<Member | null>(null);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);

    const [memberToDelete, setMemberToDelete] = useState<Member | null>(null);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);

    const handleView = (member: Member) => {
        setViewingMember(member);
        setIsViewDialogOpen(true);
    };

    const handleEdit = (member: Member) => {
        setEditingMember(member);
        setIsEditDialogOpen(true);
    };

    const confirmDelete = (member: Member) => {
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

    return (
        <>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="min-w-[200px]">Member</TableHead>
                        <TableHead>Role(s)</TableHead>
                        <TableHead className="hidden lg:table-cell">Permissions</TableHead>
                        <TableHead className="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {members.data.map((member: Member) => (
                        <TableRow key={member.uuid}>
                            <TableCell className="max-w-[250px] truncate font-medium" title={member.name}>
                                <div className="flex items-center gap-3">
                                    <UserInfo user={member} showEmail />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div className="flex flex-wrap gap-1 max-w-[200px]">
                                    {member.roles.slice(0, 2).map((role: string) => (
                                        <Badge key={role} variant="secondary" className="capitalize">
                                            {role}
                                        </Badge>
                                    ))}
                                    {member.roles.length > 2 && (
                                        <Badge variant="outline" className="text-muted-foreground">
                                            +{member.roles.length - 2} more
                                        </Badge>
                                    )}
                                    {member.roles.length === 0 && (
                                        <span className="text-xs text-muted-foreground italic text-nowrap">No roles</span>
                                    )}
                                </div>
                            </TableCell>
                            <TableCell className="hidden lg:table-cell">
                                <div className="flex flex-wrap gap-1 max-w-[200px]">
                                    {member.permissions.slice(0, 3).map((permission: string) => (
                                        <span key={permission} className="text-[10px] text-muted-foreground bg-secondary/50 px-1 rounded">
                                            {permission}
                                        </span>
                                    ))}
                                    {member.permissions.length > 3 && (
                                        <span className="text-[10px] text-muted-foreground">
                                            +{member.permissions.length - 3} more
                                        </span>
                                    )}
                                </div>
                            </TableCell>
                            <TableCell className="text-right">
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
                                        <DropdownMenuItem onClick={() => handleView(member)}>
                                            <Eye className="mr-2 h-4 w-4" />
                                            <span>View</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onClick={() => handleEdit(member)}>
                                            <Edit className="mr-2 h-4 w-4" />
                                            <span>Edit</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem 
                                            variant="destructive"
                                            onClick={() => confirmDelete(member)}
                                        >
                                            <Trash2 className="mr-2 h-4 w-4" />
                                            <span>Delete</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            <div className="py-4 border-t px-4">
                <DataTablePagination links={members.links} />
            </div>

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
