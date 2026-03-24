<?php

namespace App\Http\Requests\TenantMember;

use App\Services\ActiveTenant;
use Illuminate\Foundation\Http\FormRequest;

class ViewMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user->can('viewMembers', app(ActiveTenant::class)->getOrFail());
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
