<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Policies\TenantPolicy;
use Illuminate\Foundation\Http\FormRequest;

class LeaveTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        /** @var Tenant $tenant */
        $tenant = $this->route('tenant');

        return $user->can('leave', $tenant);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
