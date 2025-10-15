<?php

namespace App\Domain\Post\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nickname')) {
            $this->merge([
                'nickname' => strtolower($this->input('nickname')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nickname' => [
                'required',
                'string',
                'unique:members_profile,nickname',
                'min:3',
                'max:32',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'email' => 'required|string|email|max:64|unique:users',
            'password' => 'required|string|min:8|max:32|confirmed',
            'region_id' => 'nullable|integer|exists:geo_regions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.regex' => 'The nickname may only contain letters and numbers.',
        ];
    }
}
