import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import tenant from '@/routes/tenant';
import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { type Member } from '@/components/custom/tables/members-table';

interface Role {
    id: number;
    name: string;
}
interface EditMemberDialogProps {
    member: Member | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    availableRoles: string[];
    availablePermissions: string[];
}

export function EditMemberDialog({
    member,
    open,
    onOpenChange,
    availableRoles,
    availablePermissions,
}: EditMemberDialogProps) {
    const { data, setData, put, processing, reset } = useForm({
        roles: [] as string[],
        permissions: [] as string[],
    });

    useEffect(() => {
        if (member) {
            setData({
                roles: member.roles,
                permissions: member.permissions,
            });
        }
    }, [member]);

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();
        if (!member) return;

        put((tenant.members as any).update.url({ user: member.id }), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[425px]">
                <form onSubmit={handleUpdate}>
                    <DialogHeader>
                        <DialogTitle>Edit Member</DialogTitle>
                        <DialogDescription>
                            Assign roles and permissions to {member?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <section className="space-y-3">
                            <h4 className="text-sm font-medium leading-none">Roles</h4>
                            <div className="grid grid-cols-2 gap-2">
                                {availableRoles.map((role) => (
                                    <div key={role} className="flex items-center space-x-2">
                                        <Checkbox 
                                            id={`role-${role}`}
                                            checked={data.roles.includes(role)}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    setData('roles', [...data.roles, role]);
                                                } else {
                                                    setData('roles', data.roles.filter((r) => r !== role));
                                                }
                                            }}
                                        />
                                        <Label htmlFor={`role-${role}`} className="capitalize cursor-pointer">
                                            {role.replace('_', ' ')}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </section>
                        <section className="space-y-3">
                            <h4 className="text-sm font-medium leading-none">Permissions</h4>
                            <div className="grid gap-2">
                                {availablePermissions.map((permission) => (
                                    <div key={permission} className="flex items-center space-x-2">
                                        <Checkbox 
                                            id={`perm-${permission}`}
                                            checked={data.permissions.includes(permission)}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    setData('permissions', [...data.permissions, permission]);
                                                } else {
                                                    setData('permissions', data.permissions.filter((p) => p !== permission));
                                                }
                                            }}
                                        />
                                        <Label htmlFor={`perm-${permission}`} className="text-xs cursor-pointer">
                                            {permission.replace(/_/g, ' ')}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            Save changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
