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
            $category = $this->input('category');
            $input['category'] = is_numeric($category) ? (int) $category : $category;
        }

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
            'category' => ['nullable', 'string', Rule::exists('feed_categories', 'key')],
            'sort-by' => ['nullable', 'string', Rule::in(['thumbs-up', 'thumbs-down', 'recent', 'oldest'])],
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

            'sort-by.string' => 'The sort-by value must be a string.',
            'sort-by.in' => 'The sort-by option must be one of: thumbs-up, thumbs-down, recent, oldest.',
        ];
    }
}
