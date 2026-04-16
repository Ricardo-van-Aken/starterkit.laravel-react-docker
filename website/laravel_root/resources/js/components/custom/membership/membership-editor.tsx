import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { RoleEditor } from './role-editor';
import { PermissionEditor } from './permission-editor';
import { Shield, UserPlus } from 'lucide-react';
import { type Abilities } from '@/types';

interface MembershipEditorProps {
    data: {
        roles: string[];
        permissions: string[];
    };
    setData: (key: string, value: string[]) => void;
    availableRoles: string[];
    manageableRoles: string[];
    availablePermissions: string[];
}

export function MembershipEditor({
    data,
    setData,
    availableRoles,
    manageableRoles,
    availablePermissions,
}: MembershipEditorProps) {
    return (
        <Tabs defaultValue="roles" className="w-full">
            <TabsList className="grid w-full grid-cols-2">
                <TabsTrigger value="roles" className="flex items-center gap-2">
                    <UserPlus className="size-4" />
                    <span>Roles</span>
                </TabsTrigger>
                <TabsTrigger value="permissions" className="flex items-center gap-2">
                    <Shield className="size-4" />
                    <span>Permissions</span>
                </TabsTrigger>
            </TabsList>
            
            <TabsContent value="roles" className="mt-4 space-y-4">
                <div className="text-sm text-muted-foreground mb-2">
                    Select the high-level roles for this membership.
                </div>
                <RoleEditor
                    selectedRoles={data.roles}
                    onChange={(roles) => setData('roles', roles)}
                    availableRoles={availableRoles}
                    manageableRoles={manageableRoles}
                />
            </TabsContent>
            
            <TabsContent value="permissions" className="mt-4 space-y-4">
                <div className="text-sm text-muted-foreground mb-2">
                    Assign specific direct permissions. These are additive to any permissions granted by roles.
                </div>
                <PermissionEditor
                    selectedPermissions={data.permissions}
                    onChange={(permissions) => setData('permissions', permissions)}
                    availablePermissions={availablePermissions}
                />
            </TabsContent>
        </Tabs>
    );
}
