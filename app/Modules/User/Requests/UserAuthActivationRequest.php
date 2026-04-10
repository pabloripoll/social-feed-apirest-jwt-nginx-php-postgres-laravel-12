<?php

namespace App\Modules\User\Requests;

use App\Modules\User\Models\UserActivationCode;
use Illuminate\Foundation\Http\FormRequest;

class UserAuthActivationRequest extends FormRequest
{
    protected ?UserActivationCode $activationCode = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Merge route parameters into validation data
     */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'code' => $this->route('code'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|size:32|exists:users_activation_codes,code',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The activation code is required.',
            'code.string' => 'The activation code must be a valid string.',
            'code.size' => 'The activation code must be exactly 32 characters.',
            'code.exists' => 'Invalid or expired activation code.',
        ];
    }

    /**
     * Called after validation passes
     */
    protected function passedValidation(): void
    {
        // Load the entity once validation passes
        $this->activationCode = UserActivationCode::where('code', $this->validated('code'))
            ->with('user')
            ->first();
    }

    /**
     * Get the UserActivationCode entity (already loaded after validation)
     */
    public function getActivationCode(): ?UserActivationCode
    {
        return $this->activationCode;
    }
}
