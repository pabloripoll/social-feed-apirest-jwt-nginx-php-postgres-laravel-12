<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserMemberRequest extends FormRequest
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

        // normalize sort-by
        if ($this->has('sort-by')) {
            $input['sort-by'] = strtolower((string) $this->input('sort-by'));
        }

        $this->merge($input);
    }

    /**
     * Validation rules for the query params used to list/filter posts
     */
    public function rules(): array
    {
        return [
            'sort-by' => ['nullable', 'string', Rule::in(['recent', 'oldest'])],
        ];
    }

    /**
     * Friendly messages for validation failures
     */
    public function messages(): array
    {
        return [
            'sort-by.string' => 'The sort-by value must be a string.',
            'sort-by.in' => 'The sort-by option must be one of: thumbs-up, thumbs-down, recent, oldest.',
        ];
    }
}
