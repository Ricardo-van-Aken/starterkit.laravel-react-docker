<?php

namespace App\Http\Requests\TenantMember;

use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();
        $tenant = app(ActiveTenant::class)->getOrFail();

        /** @var \App\Models\User $memberUser */
        $memberUser = $this->route('user');

        return $user->can('update', [TenantMemberPolicy::class, $memberUser, $tenant, $this->input('roles', [])]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array'], 
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
