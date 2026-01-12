<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserModerationSanctionCreateRequest extends FormRequest
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
        $sanctionId = $this->route('sanction') ?? $this->route('id');

        return [
            'key' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/', // Only lowercase letters, numbers, and underscores
                Rule::unique('users_moderation_sanctions', 'key')->ignore($sanctionId),
            ],
            'position' => [
                'required',
                'integer',
                'min:0',
            ],
            'title' => [
                'required',
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
            'key. required' => 'The sanction key is required.',
            'key.string' => 'The sanction key must be a string.',
            'key.max' => 'The sanction key must not exceed 50 characters.',
            'key. regex' => 'The sanction key may only contain lowercase letters, numbers, and underscores.',
            'key.unique' => 'This sanction key already exists.',

            'position.required' => 'The display position is required.',
            'position. integer' => 'The display position must be a number.',
            'position.min' => 'The display position must be 0 or greater.',

            'title.required' => 'The sanction title is required.',
            'title.string' => 'The sanction title must be a string.',
            'title.max' => 'The sanction title must not exceed 100 characters.',

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
            'key' => 'sanction key',
            'position' => 'display position',
            'title' => 'sanction title',
            'description' => 'sanction description',
        ];
    }
}
