<?php

namespace App\Domain\Member\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class MemberFeedMediaRequest extends FormRequest
{
    protected $allowedMedia;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->allowedMedia = env('FEED_POST_MEDIA_ALLOWED');

        return parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => ['required', 'string', Rule::in($this->allowedMedia)],
            'media' => ['required', 'file',],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'A type is required.',
            'type.string' => 'The type value must be a string.',
            'type.in' => 'The type value must be from allowed media option: ' . $this->allowedMedia,

            'file.required' => 'A file is required.',
            'file.file' => 'The uploaded content must be a valid file.',
            //'file.mimes' => 'Allowed types: images (jpeg, png, jpg, gif, svg, webp), videos (mp4, mov, avi, wmv, flv, mkv), audio (mp3, wav, ogg, aac, flac).',
            'file.max' => 'Maximum file size is 3 MB.',
        ];
    }
}
