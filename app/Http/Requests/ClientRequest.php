<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $clientId = $this->route('client') ? $this->route('client')->id : null;

        return [
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $clientId,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:512',
        ];
    }
}
