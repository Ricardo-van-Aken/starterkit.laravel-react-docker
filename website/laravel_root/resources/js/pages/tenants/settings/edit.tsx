import TenantController from '@/actions/App/Http/Controllers/TenantController';
import { type BreadcrumbItem, type SharedData, type Tenant } from '@/types';
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

export default function TenantSettings({
    tenant,
}: {
    tenant: Tenant;
}) {
    usePage<SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenant settings" />

            <TenantSettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Tenant information"
                        description="Update your tenant's name and details"
                    />

                    <Form
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
                </div>
            </TenantSettingsLayout>
        </AppLayout>
    );
}
