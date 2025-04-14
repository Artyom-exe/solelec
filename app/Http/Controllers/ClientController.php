<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Affiche la liste des clients
     */
    public function index()
    {
        $clients = Client::with('quotes')->latest()->get();

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients
        ]);
    }

    /**
     * Affiche le formulaire de création d'un client
     */
    public function create()
    {
        return Inertia::render('Admin/Clients/Create');
    }

    /**
     * Enregistre un nouveau client dans la base de données
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clients,email|max:255',
            'adress' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        // Journalisation de l'activité
        ActivityLogger::log('create', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' ajouté');

        return redirect()->route('clients')->with('success', 'Client ajouté avec succès');
    }

    /**
     * Affiche les détails d'un client
     */
    public function show(Client $client)
    {
        $client->load(['quotes', 'quotes.services']);

        return Inertia::render('Admin/Clients/Show', [
            'client' => $client
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un client
     */
    public function edit(Client $client)
    {
        return Inertia::render('Admin/Clients/Edit', [
            'client' => $client
        ]);
    }

    /**
     * Met à jour les informations d'un client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clients,email,'.$client->id,
            'adress' => 'nullable|string',
        ]);

        $client->update($validated);

        // Journalisation de l'activité
        ActivityLogger::log('update', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' mis à jour');

        return redirect()->route('clients')->with('success', 'Client mis à jour avec succès');
    }

    /**
     * Supprime un client
     */
    public function destroy(Client $client)
    {
        // Vérifier si le client a des devis ou interventions avant de supprimer
        if ($client->quotes()->count() > 0) {
            return redirect()->back()->with('error', 'Ce client ne peut pas être supprimé car il possède des devis associés.');
        }

        // Récupérer le nom du client avant de le supprimer pour la journalisation
        $clientName = $client->name . ' ' . $client->lastname;

        $client->delete();

        // Journalisation de l'activité
        // Note: nous passons le client même s'il est supprimé car il contient encore son ID
        ActivityLogger::log('delete', $client, 'Client ' . $clientName . ' supprimé');

        return redirect()->route('clients')->with('success', 'Client supprimé avec succès');
    }
}
