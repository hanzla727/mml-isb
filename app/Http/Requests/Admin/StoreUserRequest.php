<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8'],
            'pin' => ['nullable', 'digits_between:4,6'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'na_head', 'uc_head', 'user'])],
            'na_id' => ['nullable', 'exists:nas,id'],
            'na_ids' => ['nullable', 'array'],
            'na_ids.*' => ['exists:nas,id'],
            'uc_id' => ['nullable', 'exists:ucs,id'],
            'uc_ids' => ['nullable', 'array'],
            'uc_ids.*' => ['exists:ucs,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'reporting_head_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
        ];
    }
}
