<?php

namespace App\Domain\Feed\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedReportRequest extends FormRequest
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

        // key cast to int when present
        if ($this->has('key')) {
            $key = $this->input('key');
            $input['key'] = is_numeric($key) ? (int) $key : $key;
        }

        $this->merge($input);
    }

    /**
     * Validation rules for the query params used to report a feed post
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'regex:/^[a-z-]+$/',],
        ];
    }

    /**
     * Friendly messages for validation failures
     */
    public function messages(): array
    {
        return [
            'key.required' => 'The key value must be sent.',
            'key.string' => 'The key must be an string.',
            'key.regex' => 'The selected key key does not exist.',
        ];
    }
}
