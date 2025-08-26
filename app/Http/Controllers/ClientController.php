<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\ClientRequest;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->get();
        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients
        ]);
    }

    public function store(ClientRequest $request)
    {
        $validated = $request->validated();

        $client = Client::create($validated);
    ActivityLogger::log('create', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' ajouté');

        return back()->with([
            'success' => 'Client ajouté avec succès',
            'client' => $client
        ]);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $validated = $request->validated();

        $client->update($validated);
    ActivityLogger::log('update', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' mis à jour');

        return back()->with('success', 'Client mis à jour avec succès');
    }

    public function destroy(Client $client)
    {
        if ($client->quotes()->count() > 0) {
            return back()->with('error', 'Ce client ne peut pas être supprimé car il possède des devis associés.');
        }

        // Stocker les informations du client AVANT de le supprimer
        $clientName = $client->name . ' ' . $client->lastname;

        // Logger l'activité AVANT de supprimer le client
        ActivityLogger::log('delete', $client, 'Client ' . $clientName . ' supprimé');

        // Maintenant on peut supprimer le client
        $client->delete();

        return back()->with('success', 'Client supprimé avec succès');
    }
}
