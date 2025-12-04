<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAuthActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:64|exists:users',
            'code' => 'required|string|exists:members_activation_codes',
        ];
    }
}
