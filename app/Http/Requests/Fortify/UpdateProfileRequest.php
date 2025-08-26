<?php

namespace App\Http\Requests\Fortify;

use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateProfileRequest
{
    public static function rules(User $user): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];
    }
}
