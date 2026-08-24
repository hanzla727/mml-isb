<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVolunteerDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(['cnic', 'certificate', 'agreement', 'training', 'other'])],
            'file' => ['required', 'file', 'max:10240'],
        ];
    }
}
