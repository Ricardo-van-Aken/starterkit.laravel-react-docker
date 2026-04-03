import * as React from "react"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog"
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
        <Dialog>
            <DialogTrigger render={triggerElement} />
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>

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

                            <DialogFooter className="gap-2">
                                <DialogClose
                                    render={
                                        <Button variant="secondary" onClick={() => resetAndClearErrors()}>
                                            {cancelText}
                                        </Button>
                                    }
                                />

                                <Button variant={variant} disabled={processing} type="submit">
                                    {confirmText}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    )
}

