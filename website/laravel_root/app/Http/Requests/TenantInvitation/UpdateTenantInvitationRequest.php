<?php

namespace App\Http\Requests\TenantInvitation;

use App\Models\TenantInvitation;
use App\Policies\TenantInvitationPolicy;
use App\Rules\TenantPermissionRule;
use App\Rules\TenantRoleRule;
use App\Services\ActiveTenant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();
        /** @var TenantInvitation $invitation */
        $invitation = $this->route('tenantInvitation');

        return $user->can('update', [$invitation, $this->input('roles', [])]);
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
            'roles'         => ['required', 'array'],
            'roles.*'       => ['string', new TenantRoleRule($tenant)],
            'permissions'   => ['present', 'array'],
            'permissions.*' => ['string', new TenantPermissionRule],
        ];
    }
}
