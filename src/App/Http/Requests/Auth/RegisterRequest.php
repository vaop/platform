<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use System\Settings\RegistrationSettings;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(RegistrationSettings::class)->registrationOpen;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $settings = app(RegistrationSettings::class);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'country_id' => ['nullable', 'integer', 'exists:geography_countries,id'],
            'timezone' => ['nullable', 'string', 'timezone'],
        ];

        if ($settings->requiresLegalAcceptance()) {
            $rules['terms'] = ['accepted'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'terms.accepted' => __('auth.terms_required'),
        ];
    }
}
