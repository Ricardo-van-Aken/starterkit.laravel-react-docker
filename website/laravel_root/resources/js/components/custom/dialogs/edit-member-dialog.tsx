import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import tenant from '@/routes/tenant';
import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { type MemberWithAbilities } from '@/components/custom/tables/members-table';

import { MembershipEditor } from '@/components/custom/membership/membership-editor';

interface EditMemberDialogProps {
    member: MemberWithAbilities | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    availableRoles: string[];
    manageableRoles: string[];
    availablePermissions: string[];
}

export function EditMemberDialog({
    member,
    open,
    onOpenChange,
    availableRoles,
    manageableRoles,
    availablePermissions,
}: EditMemberDialogProps) {
    const { data, setData, patch, processing, reset } = useForm({
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

        patch((tenant.members as any).update.url({ user: member.uuid }), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[700px]">
                <form onSubmit={handleUpdate}>
                    <DialogHeader>
                        <DialogTitle>Edit Member</DialogTitle>
                        <DialogDescription>
                            Assign roles and permissions to {member?.name}.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <div className="py-2">
                        <MembershipEditor
                            data={data}
                            setData={(key, value) => setData(key as 'roles' | 'permissions', value)}
                            availableRoles={availableRoles}
                            manageableRoles={manageableRoles}
                            availablePermissions={availablePermissions}
                        />
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
