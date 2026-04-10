<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserAdminRequest extends FormRequest
{
    /**
     * Anyone can list posts
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Normalize query inputs and apply sensible defaults
     */
    public function prepareForValidation(): void
    {
        $input = $this->all();

        // normalize sort_by
        if ($this->has('sort_by')) {
            $input['sort_by'] = strtolower((string) $this->input('sort_by'));
        }

        $this->merge($input);
    }

    /**
     * Validation rules for the query params used to list/filter posts
     */
    public function rules(): array
    {
        return [
            'sort_by' => ['nullable', 'string', Rule::in(['recent', 'oldest'])],
        ];
    }

    /**
     * Friendly messages for validation failures
     */
    public function messages(): array
    {
        return [
            'sort_by.string' => 'The sort_by value must be a string.',
            'sort_by.in' => 'The sort_by option must be one of: thumbs-up, thumbs-down, recent, oldest.',
        ];
    }
}
