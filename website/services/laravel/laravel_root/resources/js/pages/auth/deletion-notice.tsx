import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';
import { restore } from '@/routes/deletion';
import { Form, Head } from '@inertiajs/react';

export default function DeletionNotice({
    scheduledForDeletionAt,
}: {
    scheduledForDeletionAt: string;
}) {
    // Format the date for the user
    const date = new Date(scheduledForDeletionAt).toLocaleDateString(undefined, {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    return (
        <AuthLayout
            title="Account Pending Deletion"
            description={`Your account is scheduled to be permanently deleted on ${date}. During this holding period, your access to the application has been suspended.`}
        >
            <Head title="Account Scheduled for Deletion" />

            <div className="mb-6 rounded-md bg-amber-50 p-4 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                <p>
                    If you change your mind, you can cancel the deletion process and restore full access to your account and tenant resources immediately.
                </p>
            </div>

            <div className="flex flex-col space-y-4 text-center">
                <Form {...restore.form()}>
                    {({ processing }) => (
                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            Cancel Deletion & Restore Account
                        </Button>
                    )}
                </Form>

                <Form {...logout.form()}>
                    {({ processing }) => (
                        <Button
                            type="submit"
                            variant="secondary"
                            className="w-full"
                            disabled={processing}
                        >
                            Log Out
                        </Button>
                    )}
                </Form>
            </div>
        </AuthLayout>
    );
}
