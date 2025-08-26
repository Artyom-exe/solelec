<?php

namespace App\Validators;

use App\Models\User;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\Jetstream;

class FortifyValidationRules
{
    /**
     * Rules for updating a user's profile information.
     */
    public static function profileRules(User $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];
    }

    /**
     * Rules for creating a new user (password is injected by caller).
     */
    public static function createUserRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'password' => provided by caller
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];
    }

    /**
     * Rules for updating password (caller provides the password rules array).
     */
    public static function updatePasswordRules(array $passwordRules): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $passwordRules,
        ];
    }

    /**
     * Rules for resetting password (caller provides the password rules array).
     */
    public static function resetPasswordRules(array $passwordRules): array
    {
        return [
            'password' => $passwordRules,
        ];
    }
}
