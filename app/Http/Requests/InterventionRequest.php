<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InterventionRequest extends FormRequest
{
    public function authorize()
    {
        // Seules les personnes autorisées devraient appeler ce FormRequest via middleware
        return auth()->check();
    }

    public function rules()
    {
        return [
            // accept either an existing client id or new client data
            'clients_id' => 'required_without:new_client_name|nullable|exists:clients,id',
            'devis_id' => 'nullable|exists:quotes,id',
            'new_client_name' => 'required_without:clients_id|nullable|string|max:255',
            'new_client_lastname' => 'nullable|string|max:255',
            'new_client_phone' => 'nullable|string|max:50',
            'new_client_email' => 'nullable|email|max:255',
            'new_client_adress' => 'nullable|string|max:255',
            'status' => 'required|string|in:en_attente,planifiée,en_cours,terminée,annulée',
            'scheduled_at' => 'nullable|date',
            'description' => 'nullable|string',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:intervention_images,id',
        ];
    }
}
