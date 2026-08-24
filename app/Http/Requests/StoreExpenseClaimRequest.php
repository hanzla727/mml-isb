<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreExpenseClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_type' => ['required', Rule::in(['travel', 'supplies', 'food', 'accommodation', 'other'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            // A photo of the physical receipt is mandatory — no claim without proof.
            'receipt' => ['required', File::image()->max(10 * 1024)],
        ];
    }
}
