<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserModerationUpdateRequest extends FormRequest
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

        if ($this->has('sanction')) {
            $input['sanction'] = strtolower((string) $this->input('sanction'));
        }

        if ($this->has('status')) {
            $input['status'] = strtolower((string) $this->input('status'));
        }

        if ($this->has('sanction_ends') && $this->input('sanction_ends')) {
            $input['sanction_ends'] = $this->normalizeDateToYmd($this->input('sanction_ends'));
        }

        $this->merge($input);
    }

    /**
     * Validation rules for the query params used to list/filter posts
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', Rule::exists('users_moderation_categories', 'key')],
            'sanction' => ['nullable', 'string', Rule::exists('users_moderation_sanctions', 'key')],
            'status' => ['nullable', 'string', Rule::in(['review', 'resolve', 'close'])],
            'sanction_ends' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }

    /**
     * Friendly messages for validation failures
     */
    public function messages(): array
    {
        return [
            'category.string' => 'The category must be a string.',
            'category.exists' => 'The selected category key does not exist.',

            'sanction.string' => 'The sanction must be a string.',
            'sanction.exists' => 'The selected sanction key does not exist.',

            'status.string' => 'The status value must be a string.',
            'status.in' => 'The status must be one of: review, resolve, close.',

            'sanction_ends.date_format' => 'The sanction end date must be in Y-m-d format (e.g., 2026-01-08).',
            'sanction_ends.after_or_equal' => 'The sanction end date must be today or a future date.',
        ];
    }

    /**
     * Normalize various date formats to Y-m-d
     */
    private function normalizeDateToYmd(mixed $date): ?string
    {
        if (empty($date) || ! is_string($date)) {
            return null;
        }

        try {
            // Try to parse the date and convert to Y-m-d
            $parsed = \Carbon\Carbon::parse($date);

            return $parsed->format('Y-m-d');
        } catch (\Exception $e) {
            // Return as-is if parsing fails; validation will catch invalid format
            return $date;
        }
    }
}
