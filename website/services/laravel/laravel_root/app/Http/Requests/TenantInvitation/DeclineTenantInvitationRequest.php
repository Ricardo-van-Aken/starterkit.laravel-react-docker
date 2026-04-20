<?php

namespace App\Http\Requests\TenantInvitation;

use App\Models\TenantInvitation;
use Illuminate\Foundation\Http\FormRequest;

class DeclineTenantInvitationRequest extends FormRequest
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

        return $user->can('decline', $invitation);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
