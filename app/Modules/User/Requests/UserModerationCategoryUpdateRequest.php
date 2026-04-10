<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserModerationCategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add your authorization logic here (e.g., only admins can manage categories)
        return true;
    }

    /**
     * Normalize inputs before validation.
     */
    public function prepareForValidation(): void
    {
        $input = $this->all();

        // Normalize key to lowercase and replace spaces with underscores
        if ($this->has('key')) {
            $key = strtolower((string) $this->input('key'));
            $input['key'] = str_replace(' ', '_', $key);
        }

        // Ensure level is an integer
        if ($this->has('level')) {
            $input['level'] = (int) $this->input('level');
        }

        // Ensure position is an integer
        if ($this->has('position')) {
            $input['position'] = (int) $this->input('position');
        }

        $this->merge($input);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category') ?? $this->route('id');

        return [
            'key' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/', // Only lowercase letters, numbers, and underscores
                Rule::unique('users_moderation_categories', 'key')->ignore($categoryId),
            ],
            'level' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'title' => [
                'nullable',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'key.string' => 'The category key must be a string.',
            'key.max' => 'The category key must not exceed 50 characters.',
            'key. regex' => 'The category key may only contain lowercase letters, numbers, and underscores.',
            'key.unique' => 'This category key already exists.',

            'level.integer' => 'The severity level must be a number.',
            'level.min' => 'The severity level must be at least 1.',
            'level.max' => 'The severity level must not exceed 10.',

            'position. integer' => 'The display position must be a number.',
            'position.min' => 'The display position must be 0 or greater.',

            'title.string' => 'The category title must be a string.',
            'title.max' => 'The category title must not exceed 100 characters.',

            'description. string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'key' => 'category key',
            'level' => 'severity level',
            'position' => 'display position',
            'title' => 'category title',
            'description' => 'category description',
        ];
    }
}
