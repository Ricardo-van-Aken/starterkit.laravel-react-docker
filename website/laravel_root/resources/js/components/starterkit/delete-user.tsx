import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import HeadingSmall from '@/components/starterkit/heading-small';
import { Button } from '@/components/ui/button';
import { ConfirmPasswordDialog } from '@/components/custom/dialogs/confirm-password-dialog';
import { Checkbox } from '@/components/ui/checkbox';

export default function DeleteUser() {
    return (
        <div className="space-y-6">
            <HeadingSmall
                title="Delete account"
                description="Delete your account and all of its resources"
            />
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">Warning</p>
                    <p className="text-sm">
                        Please proceed with caution, this cannot be undone.
                    </p>
                </div>

                <ConfirmPasswordDialog
                    title="Are you sure you want to delete your account?"
                    description="Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
                    confirmText="Delete account"
                    variant="destructive"
                    form={ProfileController.destroy.form()}
                    trigger="Delete account"
                    triggerProps={{
                        "data-test": "delete-user-button",
                    }}
                >
                    <div className="flex items-center space-x-2 rounded-md border p-3 bg-muted/50 border-destructive/20 text-destructive/80 mt-2">
                        <Checkbox 
                            id="force_delete_tenants" 
                            name="force_delete_tenants" 
                            value="1"
                            className="border-destructive/30 data-[state=checked]:bg-destructive data-[state=checked]:text-destructive-foreground focus-visible:ring-destructive"
                        />
                        <label
                            htmlFor="force_delete_tenants"
                            className="text-sm font-medium leading-tight peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            <span className="block mb-0.5">Force delete orphaned tenants</span>
                            <span className="font-normal text-xs text-muted-foreground block text-foreground/80">
                                Automatically delete any tenants where I am the last administrator. If unchecked, my account deletion will be blocked if I leave orphaned tenants.
                            </span>
                        </label>
                    </div>
                </ConfirmPasswordDialog>
            </div>
        </div>
    );
}

