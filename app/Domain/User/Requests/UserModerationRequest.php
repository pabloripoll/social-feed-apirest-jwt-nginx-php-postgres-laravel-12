<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserModerationRequest extends FormRequest
{
    /**
     * Only authenticated can list posts
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize query inputs and apply sensible defaults
     */
    public function prepareForValidation(): void
    {
        $input = $this->all();

        if ($this->has('category')) {
            $input['category'] = strtolower((string) $this->input('category'));
        }

        if ($this->has('status')) {
            $input['status'] = strtolower((string) $this->input('status'));
        }

        if ($this->has('sort_by')) {
            $input['sort_by'] = strtolower((string) $this->input('sort_by'));
        }

        if ($this->has('moderator')) {
            $input['moderator'] = strtolower((string) $this->input('moderator'));
        }

        $this->merge($input);
    }

    /**
     * Validation rules for the query params used to list/filter posts
     */
    public function rules(): array
    {
        return [
            'category'  => ['nullable', 'string', Rule::exists('users_moderation_categories', 'key')],
            'status'    => ['nullable', 'string', Rule::in(['opened', 'reviewing', 'resolved', 'closed'])],
            'sort_by'   => ['nullable', 'string', Rule::in(['recent', 'oldest'])],
            'moderator' => ['nullable', 'string', Rule::in(['me', 'all'])],
        ];
    }

    /**
     * Friendly messages for validation failures
     */
    public function messages(): array
    {
        return [
            'category.string' => 'The category id must be an string.',
            'category.exists' => 'The selected category key does not exist.',

            'status.string' => 'The status value must be a string.',
            'status.in' => 'The status must be one of: review, resolve, close.',

            'sort_by.string' => 'The sort_by value must be a string.',
            'sort_by.in' => 'The sort_by option must be one of: recent, oldest.',

            'moderator.string' => 'The moderator value must be a string.',
            'moderator.in' => 'The moderator must be one of: me, all.',
        ];
    }
}
