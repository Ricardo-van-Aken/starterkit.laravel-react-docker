<?php

namespace App\Http\Requests\TenantMember;

use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use Illuminate\Foundation\Http\FormRequest;

class DestroyTenantMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        $tenant = app(ActiveTenant::class)->getOrFail();

        /** @var User $memberUser */
        $memberUser = $this->route('user');

        // Check if user to be deleted is a member of the tenant
        if (! $tenant->users()->where('users.id', $memberUser->id)->exists()) {
            abort(404, 'User not found in this tenant.');
        }

        return $user->can('delete', [TenantMemberPolicy::class, $memberUser, $tenant]);
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
