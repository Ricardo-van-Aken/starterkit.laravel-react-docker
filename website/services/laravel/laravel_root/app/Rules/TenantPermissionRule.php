<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TenantPermissionRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = \App\Models\Permission::where('name', $value)
            ->where('guard_name', 'tenant')
            ->exists();

        if (! $exists) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
        }
    }
}
