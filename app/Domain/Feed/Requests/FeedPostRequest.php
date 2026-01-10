<?php

namespace App\Domain\Feed\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedPostRequest extends FormRequest
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

        // category cast to int when present
        if ($this->has('category')) {
            $input['category'] = strtolower((string) $this->input('category'));
        }

        // normalize sort_by
        if ($this->has('sort_by')) {
            $input['sort_by'] = strtolower((string) $this->input('sort_by'));
        }

        // trim search terms
        if ($this->has('search')) {
            $input['search'] = strtolower(trim((string) $this->input('search')));
        }

        $this->merge($input);
    }

    /**
     * Validation rules for the query params used to list/filter posts
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', Rule::exists('feed_categories', 'key')],
            'sort_by' => ['nullable', 'string', Rule::in(['thumbs-up', 'thumbs-down', 'recent', 'oldest'])],
            'search' => ['nullable', 'string', 'min:2', 'max:100'],
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

            'sort_by.string' => 'The sort_by value must be a string.',
            'sort_by.in' => 'The sort_by option must be one of: thumbs-up, thumbs-down, recent, oldest.',

            'search.string' => 'The search term must be a string.',
            'search.min' => 'The search term must be at least 2 characters.',
            'search.max' => 'The search term cannot exceed 100 characters.',
        ];
    }
}
