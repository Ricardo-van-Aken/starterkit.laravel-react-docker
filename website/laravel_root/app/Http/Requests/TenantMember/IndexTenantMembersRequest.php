<?php

namespace App\Http\Requests\TenantMember;

use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use Illuminate\Foundation\Http\FormRequest;

class IndexTenantMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        $tenant = app(ActiveTenant::class)->get();

        return $user->can('viewAny', [TenantMemberPolicy::class, $tenant]);
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
