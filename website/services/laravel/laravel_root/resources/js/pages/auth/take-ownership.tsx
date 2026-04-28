import InputError from '@/components/starterkit/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { update } from '@/routes/invitation';
import { Form, Head } from '@inertiajs/react';

interface TakeOwnershipProps {
    email: string;
    token: string;
}

export default function TakeOwnership({ email, token }: TakeOwnershipProps) {
    return (
        <AuthLayout
            title="Claim your account"
            description={`Complete your registration for ${email} by setting your name and password.`}
        >
            <Head title="Claim account" />

            <Form
                {...update.form(token)}
                resetOnSuccess={['password', 'password_confirmation']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Full name</Label>
                            <Input
                                id="name"
                                name="name"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="name"
                                placeholder="John Doe"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                tabIndex={2}
                                autoComplete="new-password"
                                placeholder="Password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">Confirm Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                tabIndex={3}
                                autoComplete="new-password"
                                placeholder="Confirm Password"
                            />
                            <InputError message={errors.password_confirmation} />
                        </div>

                        <Button
                            type="submit"
                            className="mt-4 w-full"
                            tabIndex={4}
                            disabled={processing}
                        >
                            {processing && <Spinner />}
                            Claim account
                        </Button>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
