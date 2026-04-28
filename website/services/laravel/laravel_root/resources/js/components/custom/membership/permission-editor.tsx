import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useMemo } from 'react';

interface PermissionEditorProps {
    selectedPermissions: string[];
    onChange: (permissions: string[]) => void;
    availablePermissions: string[];
}

interface PermissionGroup {
    name: string;
    permissions: string[];
}

const DEFAULT_GROUPS: Record<string, string[]> = {
    'Tenant Management': ['update_tenant_details', 'delete_tenant'],
    'Member Management': [
        'view_tenant_members',
        'invite_tenant_members',
        'update_tenant_members',
        'manage_tenant_member_roles',
        'delete_tenant_members',
    ],
    'Organisation Units': [
        'create_organisation_units',
        'view_organisation_units',
        'update_organisation_units',
        'delete_organisation_units',
    ],
    'Role Management': ['create_tenant_roles', 'view_tenant_roles', 'edit_tenant_roles'],
    'Org Unit Roles': [
        'create_org_unit_roles',
        'view_org_unit_roles',
        'update_org_unit_roles',
        'delete_org_unit_roles',
    ],
    'Billing': ['view_billing_information', 'edit_billing_information'],
};

export function PermissionEditor({
    selectedPermissions,
    onChange,
    availablePermissions,
}: PermissionEditorProps) {
    const groups = useMemo(() => {
        const result: PermissionGroup[] = [];
        const seenPermissions = new Set<string>();

        // Process pre-defined groups
        Object.entries(DEFAULT_GROUPS).forEach(([name, perms]) => {
            const groupPerms = perms.filter((p) => availablePermissions.includes(p));
            if (groupPerms.length > 0) {
                result.push({ name, permissions: groupPerms });
                groupPerms.forEach((p) => seenPermissions.add(p));
            }
        });

        // Add any remaining permissions to "Other"
        const remaining = availablePermissions.filter((p) => !seenPermissions.has(p));
        if (remaining.length > 0) {
            result.push({ name: 'Other', permissions: remaining });
        }

        return result;
    }, [availablePermissions]);

    const handlePermissionToggle = (permission: string, checked: boolean) => {
        if (checked) {
            onChange([...selectedPermissions, permission]);
        } else {
            onChange(selectedPermissions.filter((p) => p !== permission));
        }
    };

    const handleGroupToggle = (groupPermissions: string[], checked: boolean) => {
        if (checked) {
            // Add all permissions in group that aren't already selected
            const toAdd = groupPermissions.filter((p) => !selectedPermissions.includes(p));
            onChange([...selectedPermissions, ...toAdd]);
        } else {
            // Remove all permissions in group
            onChange(selectedPermissions.filter((p) => !groupPermissions.includes(p)));
        }
    };

    const isGroupFullyChecked = (groupPermissions: string[]) => {
        return groupPermissions.every((p) => selectedPermissions.includes(p));
    };

    const isGroupPartiallyChecked = (groupPermissions: string[]) => {
        const someChecked = groupPermissions.some((p) => selectedPermissions.includes(p));
        const allChecked = groupPermissions.every((p) => selectedPermissions.includes(p));
        return someChecked && !allChecked;
    };

    return (
        <div className="space-y-6 py-4 max-h-[60vh] overflow-y-auto pr-2">
            {groups.map((group) => {
                const fullyChecked = isGroupFullyChecked(group.permissions);
                const partiallyChecked = isGroupPartiallyChecked(group.permissions);

                return (
                    <div key={group.name} className="space-y-3 p-4 rounded-xl border bg-card/50">
                        <div className="flex items-center space-x-2 pb-2 border-b">
                            <Checkbox
                                id={`group-${group.name}`}
                                checked={fullyChecked}
                                onCheckedChange={(checked) => handleGroupToggle(group.permissions, checked === true)}
                                className={partiallyChecked ? 'opacity-70' : ''}
                            />
                            <Label 
                                htmlFor={`group-${group.name}`} 
                                className="text-sm font-bold uppercase tracking-wider cursor-pointer"
                            >
                                {group.name}
                            </Label>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-6 pt-1">
                            {group.permissions.map((permission) => (
                                <div key={permission} className="flex items-center space-x-2 group">
                                    <Checkbox
                                        id={`perm-${permission}`}
                                        checked={selectedPermissions.includes(permission)}
                                        onCheckedChange={(checked) => handlePermissionToggle(permission, checked === true)}
                                    />
                                    <Label 
                                        htmlFor={`perm-${permission}`} 
                                        className="text-xs font-medium cursor-pointer text-muted-foreground group-hover:text-foreground transition-colors"
                                    >
                                        {permission.replace(/_/g, ' ')}
                                    </Label>
                                </div>
                            ))}
                        </div>
                    </div>
                );
            })}
            {availablePermissions.length === 0 && (
                <div className="py-8 text-center text-muted-foreground italic">
                    No permissions available to assign.
                </div>
            )}
        </div>
    );
}
