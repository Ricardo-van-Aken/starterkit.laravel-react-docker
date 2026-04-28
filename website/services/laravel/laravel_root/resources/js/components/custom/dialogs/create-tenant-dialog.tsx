"use client"

import * as React from "react"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Field, FieldGroup } from "@/components/ui/field"
import { Form } from '@inertiajs/react';
import TenantController from '@/actions/App/Http/Controllers/TenantController';
import InputError from '@/components/starterkit/input-error';

interface CreateTenantDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function CreateTenantDialog({ open, onOpenChange }: CreateTenantDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <Form
                    {...TenantController.store.form()}
                    onSuccess={() => onOpenChange(false)}
                    resetOnSuccess
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <DialogHeader>
                                <DialogTitle>Create New Tenant</DialogTitle>
                                <DialogDescription>
                                    Add a new tenant to manage your organisation units.
                                </DialogDescription>
                            </DialogHeader>

                            <FieldGroup className="py-4">
                                <InputError message={errors.error} />

                                <Field>
                                    <Label htmlFor="name">Tenant Name</Label>
                                    <Input id="name" name="name" placeholder="e.g. Acme Corp" required autoFocus />
                                    <InputError message={errors.name} />
                                </Field>
                            </FieldGroup>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    Create Tenant
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
