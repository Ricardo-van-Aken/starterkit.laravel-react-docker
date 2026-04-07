<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TenantRoleRule implements ValidationRule
{
    public function __construct(
        protected Tenant $tenant
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = \App\Models\Role::where('name', $value)
            ->where('guard_name', 'tenant')
            ->where(function ($q) {
                $q->whereNull('team_id')
                  ->orWhere('team_id', $this->tenant->id);
            })
            ->exists();

        if (! $exists) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
        }
    }
}
