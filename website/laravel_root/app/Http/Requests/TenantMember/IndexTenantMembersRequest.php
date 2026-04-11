<?php

namespace App\Http\Requests\TenantMember;

use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use App\Enums\TenantInvitationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            // Members Table Pagination
            'mem_page' => 'integer|min:1',
            'mem_per_page' => 'integer|min:1|max:100',

            // Invitations Table Pagination
            'inv_page' => 'integer|min:1',
            'inv_per_page' => 'integer|min:1|max:100',

            // Invitations Table Sorting
            'inv_sort' => 'string|in:email,status,expires_at',
            'inv_dir' => 'string|in:asc,desc',

            // Invitations Table Filtering
            'inv_search' => 'nullable|string|max:255',
            'inv_status' => 'array',
            'inv_status.*' => [
                'string',
                Rule::in([
                    ...array_column(TenantInvitationStatus::cases(), 'value'),
                    'all'
                ])
            ],
            'inv_expires_at' => 'nullable|string', // Timeframe filter (e.g. '1_month')
        ];
    }
}
