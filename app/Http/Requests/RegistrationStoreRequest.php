<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invite_id' => ['nullable', 'integer'],
            'invite_token' => ['nullable', 'string'],
            'iframe' => ['sometimes', 'boolean'],
            'fields' => ['sometimes', 'array'], // When a form has no fields, there won't be a fields array
        ];
    }
}
