import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { type Abilities, type BreadcrumbItem, type SharedData, type Tenant } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, usePage } from '@inertiajs/react';

import HeadingSmall from '@/components/starterkit/heading-small';
import InputError from '@/components/starterkit/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import TenantSettingsLayout from '@/layouts/settings/tenant-layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenant settings',
        href: TenantController.edit.url(),
    },
];

import { LogOut, Trash2 } from 'lucide-react';
import { Separator } from '@/components/ui/separator';

import { ConfirmPasswordDialog } from '@/components/custom/dialogs/confirm-password-dialog';

interface TenantSettingsProps {
    abilities: Abilities;
}

export default function TenantSettings({ abilities }: TenantSettingsProps) {
    const { auth } = usePage<SharedData>().props;
    const tenant = auth.active_tenant!;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenant settings" />

            <TenantSettingsLayout>
                <div className="space-y-6">
                    {abilities.update && (
                        <>
                            <HeadingSmall
                                title="Tenant information"
                                description="Update your tenant's name and details"
                            />

                            <Form
                                key={tenant.uuid}
                                {...TenantController.update.form()}
                                options={{
                                    preserveScroll: true,
                                }}
                                className="space-y-6"
                            >
                                {({ processing, recentlySuccessful, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">Name</Label>

                                            <Input
                                                id="name"
                                                className="mt-1 block w-full"
                                                defaultValue={tenant.name}
                                                name="name"
                                                required
                                                autoComplete="name"
                                                placeholder="Tenant name"
                                            />

                                            <InputError
                                                className="mt-2"
                                                message={errors.name}
                                            />
                                        </div>

                                        <div className="flex items-center gap-4">
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                Save
                                            </Button>

                                            <Transition
                                                show={recentlySuccessful}
                                                enter="transition ease-in-out"
                                                enterFrom="opacity-0"
                                                leave="transition ease-in-out"
                                                leaveTo="opacity-0"
                                            >
                                                <p className="text-sm text-neutral-600">
                                                    Saved
                                                </p>
                                            </Transition>
                                        </div>
                                    </>
                                )}
                            </Form>
                            <Separator />
                        </>
                    )}

                    <div className="space-y-6">
                        <HeadingSmall
                            title="Leave Tenant"
                            description="Permanently remove your access to this tenant"
                        />
                        <div className="bg-destructive/5 dark:bg-destructive/10 border-destructive/20 rounded-lg border p-4">
                            <p className="mb-4 text-sm text-neutral-600 dark:text-neutral-400">
                                Once you leave this tenant, you will no longer have access to its resources. You will need to be re-invited to regain access.
                            </p>

                            <ConfirmPasswordDialog
                                title="Are you sure you want to leave this tenant?"
                                description="Please enter your password to confirm you would like to leave the tenant."
                                confirmText="Leave Tenant"
                                variant="destructive"
                                form={TenantController.leave.form({ tenant: tenant.uuid })}
                                trigger={
                                    <>
                                        <LogOut className="size-4" />
                                        Leave Tenant
                                    </>
                                }
                                triggerProps={{
                                    className: "gap-2"
                                }}
                            />
                        </div>
                    </div>

                    {abilities.delete && (
                        <>
                            <Separator />
                            <div className="space-y-6">
                                <HeadingSmall
                                    title="Delete Tenant"
                                    description="Permanently delete this tenant and all of its data"
                                />
                                <div className="bg-destructive/5 dark:bg-destructive/10 border-destructive/20 rounded-lg border p-4">
                                    <p className="mb-4 text-sm text-neutral-600 dark:text-neutral-400">
                                        This action is irreversible. All data associated with this tenant, including members, organisation units, and settings, will be permanently deleted.
                                    </p>

                                    <ConfirmPasswordDialog
                                        title="Are you sure you want to delete this tenant?"
                                        description="Please enter your password to confirm permanently deleting the tenant and all its data."
                                        confirmText="Delete Tenant"
                                        variant="destructive"
                                        form={TenantController.destroy.form()}
                                        trigger={
                                            <>
                                                <Trash2 className="size-4" />
                                                Delete Tenant
                                            </>
                                        }
                                        triggerProps={{
                                            className: "gap-2"
                                        }}
                                    />
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </TenantSettingsLayout>
        </AppLayout>
    );
}
