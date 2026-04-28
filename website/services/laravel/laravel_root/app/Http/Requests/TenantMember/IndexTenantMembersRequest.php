<?php

namespace App\Http\Requests\TenantMember;

use App\Models\User;
use App\Policies\TenantMemberPolicy;
use App\Services\ActiveTenant;
use App\Enums\TenantInvitationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @method array{
 *     mem_page?: int,
 *     mem_per_page?: int,
 *     mem_sort?: 'name'|'email'|'roles'|'permissions'|'created_at',
 *     mem_dir?: 'asc'|'desc',
 *     mem_search?: string|null,
 *     mem_roles?: list<string>,
 *     inv_page?: int,
 *     inv_per_page?: int,
 *     inv_sort?: 'email'|'status'|'expires_at'|'roles'|'permissions'|'created_at',
 *     inv_dir?: 'asc'|'desc',
 *     inv_search?: string|null,
 *     inv_status?: list<string>,
 *     inv_expires_at?: string|null
 * } validated()
 */
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
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // Members Table Pagination
            'mem_page' => 'integer|min:1',
            'mem_per_page' => 'integer|min:1|max:100',

            // Members Table Sorting
            'mem_sort' => 'string|in:name,email,roles,permissions,created_at',
            'mem_dir' => 'string|in:asc,desc',

            // Members Table Filtering
            'mem_search' => 'nullable|string|max:255',
            'mem_roles' => 'array',
            'mem_roles.*' => 'string',

            // Invitations Table Pagination
            'inv_page' => 'integer|min:1',
            'inv_per_page' => 'integer|min:1|max:100',

            // Invitations Table Sorting
            'inv_sort' => 'string|in:email,status,expires_at,roles,permissions,created_at',
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
