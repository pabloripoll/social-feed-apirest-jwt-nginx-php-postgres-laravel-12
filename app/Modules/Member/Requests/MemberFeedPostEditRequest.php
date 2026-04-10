<?php

namespace App\Modules\Member\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberFeedPostEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->input('status')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['broadcast', 'draft'])],
            'category_id' => ['required', 'integer', Rule::exists('feed_categories', 'id')],
            'title' => ['required', 'string', 'min:8', 'max:128'],
            'article' => ['required', 'string', 'min:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Input -status- is required and define between -broadcast- or -draft- options.',
            'status.string' => 'The status must be a string.',
            'status.in' => 'The status must be either -broadcast- or -draft- option.',
            'category_id.required' => 'An existing category id is required.',
            'category_id.integer' => 'Category id must be an integer.',
            'category_id.exists' => 'The selected category does not exist.',
            'title.required' => 'A title is required.',
            'title.min' => 'The title must be at least :min characters.',
            'title.max' => 'The title must not exceed :max characters.',
            'article.required' => 'An article body is required.',
            'article.min' => 'The article must be at least :min characters.',
        ];
    }
}
