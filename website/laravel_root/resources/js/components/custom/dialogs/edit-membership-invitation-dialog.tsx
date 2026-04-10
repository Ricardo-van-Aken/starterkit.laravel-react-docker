import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { MembershipEditor } from '@/components/custom/membership/membership-editor';
import { type MembershipInvitation } from '@/components/custom/tables/outgoing-membership-invitations-table';

interface EditMembershipInvitationDialogProps {
    invitation: MembershipInvitation | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    availableRoles: string[];
    availablePermissions: string[];
}

export function EditMembershipInvitationDialog({
    invitation,
    open,
    onOpenChange,
    availableRoles,
    availablePermissions,
}: EditMembershipInvitationDialogProps) {
    const { data, setData, patch, processing, reset } = useForm({
        roles: [] as string[],
        permissions: [] as string[],
    });

    useEffect(() => {
        if (invitation) {
            setData({
                roles: invitation.roles,
                permissions: invitation.permissions,
            });
        }
    }, [invitation]);

    const handleUpdate = (e: React.FormEvent) => {
        e.preventDefault();
        if (!invitation) return;

        patch(`/tenant/invitations/${invitation.uuid}`, {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[700px]">
                <form onSubmit={handleUpdate}>
                    <DialogHeader>
                        <DialogTitle>Edit Invitation</DialogTitle>
                        <DialogDescription>
                            Adjust the roles and permissions for {invitation?.email}.
                        </DialogDescription>
                    </DialogHeader>
                    
                    <div className="py-2">
                        <MembershipEditor
                            data={data}
                            setData={(key, value) => setData(key as 'roles' | 'permissions', value)}
                            availableRoles={availableRoles}
                            availablePermissions={availablePermissions}
                        />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
