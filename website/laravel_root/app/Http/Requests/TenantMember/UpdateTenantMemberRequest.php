<?php

namespace App\Http\Requests\TenantMember;

use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Rules\TenantPermissionRule;
use App\Rules\TenantRoleRule;
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
        $memberUser = $this->route('user');
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        return $user->can('update', [
            TenantMemberPolicy::class,
            $memberUser,
            $tenant,
            $this->input('roles'),
            $this->input('permissions'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = app(ActiveTenant::class)->get();

        return [
            'roles'         => ['sometimes', 'array'],
            'roles.*'       => ['string', new TenantRoleRule($tenant)],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', new TenantPermissionRule],
        ];
    }
}
