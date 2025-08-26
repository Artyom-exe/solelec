<?php

namespace App\Http\Requests\Fortify;

class UpdatePasswordRequest
{
    public static function rules(array $passwordRules): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $passwordRules,
        ];
    }
}
