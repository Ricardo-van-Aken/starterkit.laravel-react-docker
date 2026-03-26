import { UserInfo } from '@/components/starterkit/user-info';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { EditMemberDialog } from '@/components/custom/dialogs/edit-member-dialog';
import { type User } from '@/types';
import { MoreHorizontal, Edit, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import tenant from '@/routes/tenant';

export interface Member extends User {
    roles: string[];
    permissions: string[];
}

interface MembersTableProps {
    members: Member[];
    availableRoles: string[];
    availablePermissions: string[];
}

export function MembersTable({ members, availableRoles, availablePermissions }: MembersTableProps) {
    const [editingMember, setEditingMember] = useState<Member | null>(null);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);

    const handleEdit = (member: Member) => {
        setEditingMember(member);
        setIsEditDialogOpen(true);
    };

    const handleDelete = (member: Member) => {
        if (confirm(`Are you sure you want to remove ${member.name} from this tenant?`)) {
            router.delete((tenant.members as any).destroy.url({ user: member.id }), {
                preserveScroll: true,
            });
        }
    };

    return (
        <>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Member</TableHead>
                        <TableHead>Role(s)</TableHead>
                        <TableHead className="hidden md:table-cell">Permissions</TableHead>
                        <TableHead className="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {members.map((member) => (
                        <TableRow key={member.id}>
                            <TableCell className="font-medium">
                                <div className="flex items-center gap-3">
                                    <UserInfo user={member} showEmail />
                                </div>
                            </TableCell>
                            <TableCell>
                                <div className="flex flex-wrap gap-1">
                                    {member.roles.map((role) => (
                                        <Badge key={role} variant="secondary" className="capitalize">
                                            {role}
                                        </Badge>
                                    ))}
                                </div>
                            </TableCell>
                            <TableCell className="hidden md:table-cell">
                                <div className="flex flex-wrap gap-1 max-w-[200px]">
                                    {member.permissions.slice(0, 3).map((permission) => (
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
                                        <DropdownMenuItem onClick={() => handleEdit(member)}>
                                            <Edit className="mr-2 h-4 w-4" />
                                            <span>Edit</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem 
                                            variant="destructive"
                                            onClick={() => handleDelete(member)}
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

            <EditMemberDialog
                member={editingMember}
                open={isEditDialogOpen}
                onOpenChange={setIsEditDialogOpen}
                availableRoles={availableRoles}
                availablePermissions={availablePermissions}
            />
        </>
    );
}
