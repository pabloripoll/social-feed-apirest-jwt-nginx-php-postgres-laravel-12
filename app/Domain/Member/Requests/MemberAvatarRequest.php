<?php

namespace App\Domain\Member\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberAvatarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,jpg,svg',
                'max:1024', // 1 MB in kilobytes
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required.',
            'file.file' => 'The uploaded content must be a valid file.',
            'file.image' => 'The uploaded file must be an image.',
            'file.mimes' => 'Allowed types: jpeg, png, jpg, svg.',
            'file.max' => 'Maximum file size is 1 MB.',
        ];
    }
}
