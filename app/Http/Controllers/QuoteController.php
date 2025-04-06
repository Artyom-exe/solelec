<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quote;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    public function store(Request $request)
    {
        // 1) Valider les données du formulaire
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'lastname'         => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'adress'           => 'nullable|string',
            'description'      => 'required|string',
            'requested_date'   => 'nullable|date',
            'services'         => 'required|array',    // liste d'IDs de services
            'services.*'       => 'exists:services,id', // chaque service doit exister
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2) Créer ou récupérer le client (via l'email ou le phone par ex.)
        //    Logique: si l'email existe déjà => on update éventuellement
        //    Sinon on crée un nouveau client
        $data = $validator->validated();

        // On essaie de trouver un client par email
        $client = null;
        if (!empty($data['email'])) {
            $client = Client::where('email', $data['email'])->first();
        }
        // Sinon, on peut tenter par phone si email est vide
        if (!$client && !empty($data['phone'])) {
            $client = Client::where('phone', $data['phone'])->first();
        }

        if ($client) {
            // On peut mettre à jour le client existant si besoin
            $client->update([
                'name'     => $data['name'],
                'lastname' => $data['lastname'] ?? $client->lastname,
                'phone'    => $data['phone'] ?? $client->phone,
                'adress'   => $data['adress'] ?? $client->adress,
            ]);
        } else {
            // Création d'un nouveau client
            $client = Client::create([
                'name'     => $data['name'],
                'lastname' => $data['lastname'],
                'phone'    => $data['phone'],
                'email'    => $data['email'],
                'adress'   => $data['adress'],
            ]);
        }

        // 3) Créer le devis (quote) avec statut "pending"
        $quote = Quote::create([
            'client_id'      => $client->id,
            'description'    => $data['description'],
            'requested_date' => $data['requested_date'] ?? null,
            'status'         => 'pending',
        ]);

        // 4) Associer le(s) service(s) choisi(s)
        // $data['services'] est un tableau d'IDs => ex: [1, 3, 5]
        $quote->services()->attach($data['services']);

        // 5) Retourner une réponse
        return response()->json([
            'message' => 'Devis créé avec succès !',
            'quote'   => $quote->load('client', 'services'),
        ], 201);
    }
}
