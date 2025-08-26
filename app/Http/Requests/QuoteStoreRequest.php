<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuoteStoreRequest extends FormRequest
{
    public function authorize()
    {
        // Autoriser les utilisateurs authentifiés ou le public selon la route
        return true;
    }

    public function rules()
    {
        return [
            // Champs plats attendus par le modal public
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'adress' => 'nullable|string|max:255',

            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',

            'description' => 'nullable|string',

            'requested_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:requested_date',
        ];
    }

    public function messages()
    {
        return [
            'services.required' => 'Veuillez sélectionner au moins un service.',
            'services.*.exists' => 'Service invalide sélectionné.',
            'name.required' => "Le nom est requis.",
            'email.email' => "L'adresse email est invalide.",
        ];
    }
}
