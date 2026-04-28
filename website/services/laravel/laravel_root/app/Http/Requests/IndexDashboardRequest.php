<?php

namespace App\Http\Requests;

use App\Enums\TenantInvitationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @method array{
 *     status?: list<string>,
 *     sort?: 'email'|'status'|'expires_at'|'created_at',
 *     direction?: 'asc'|'desc',
 *     expires_at?: string|null
 * } validated()
 */
class IndexDashboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Any authenticated user can view their dashboard
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => 'array',
            'status.*' => [
                'string',
                Rule::in([...array_column(TenantInvitationStatus::cases(), 'value'), 'all'])
            ],
            'sort' => 'string|in:email,status,expires_at,created_at',
            'direction' => 'string|in:asc,desc',
            'expires_at' => 'nullable|string',
        ];
    }
}
