import * as React from "react"
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
    AlertDialogCancel,
} from "@/components/ui/alert-dialog"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import InputError from '@/components/starterkit/input-error';
import { Form } from '@inertiajs/react';

interface ConfirmPasswordDialogProps {
    title: string
    description: string
    confirmText?: string
    cancelText?: string
    variant?: "default" | "destructive"
    form: any // The result of Inertia useForm() or similar
    trigger: React.ReactNode
    triggerProps?: any
    children?: React.ReactNode
}

export function ConfirmPasswordDialog({
    title,
    description,
    confirmText = "Confirm",
    cancelText = "Cancel",
    variant = "default",
    form,
    trigger,
    triggerProps,
    children,
}: ConfirmPasswordDialogProps) {
    const passwordInput = React.useRef<HTMLInputElement>(null)

    const triggerElement = 
        React.isValidElement(trigger) && trigger.type !== React.Fragment ? (
            trigger
        ) : (
            <Button variant={variant} {...triggerProps}>
                {trigger}
            </Button>
        )

    return (
        <AlertDialog>
            <AlertDialogTrigger render={triggerElement} />
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>{description}</AlertDialogDescription>
                </AlertDialogHeader>

                <Form
                    {...form}
                    options={{
                        ...form.options,
                        preserveScroll: true,
                    }}
                    onError={() => {
                        passwordInput.current?.focus();
                        if (form.options?.onError) form.options.onError();
                    }}
                    resetOnSuccess
                    className="space-y-6"
                >
                    {({ processing, errors, resetAndClearErrors }) => (
                        <>
                            <InputError message={errors.error} />

                            {children && (
                                <div className="grid gap-2">
                                    {children}
                                </div>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="password" title="Password" className="sr-only">
                                    Password
                                </Label>

                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    ref={passwordInput}
                                    placeholder="Password"
                                    autoComplete="current-password"
                                />

                                <InputError message={errors.password} />
                            </div>

                            <AlertDialogFooter className="gap-2">
                                <AlertDialogCancel
                                    variant="secondary"
                                    onClick={() => resetAndClearErrors()}
                                >
                                    {cancelText}
                                </AlertDialogCancel>

                                <Button variant={variant} disabled={processing} type="submit">
                                    {confirmText}
                                </Button>
                            </AlertDialogFooter>
                        </>
                    )}
                </Form>
            </AlertDialogContent>
        </AlertDialog>
    )
}

