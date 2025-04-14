<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->get();
        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients
        ]);
    }

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
        ActivityLogger::log('create', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' ajouté');

        return back()->with([
            'success' => 'Client ajouté avec succès',
            'client' => $client
        ]);
    }

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
        ActivityLogger::log('update', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' mis à jour');

        return back()->with('success', 'Client mis à jour avec succès');
    }

    public function destroy(Client $client)
    {
        if ($client->quotes()->count() > 0) {
            return back()->with('error', 'Ce client ne peut pas être supprimé car il possède des devis associés.');
        }

        $clientName = $client->name . ' ' . $client->lastname;
        $client->delete();
        ActivityLogger::log('delete', $client, 'Client ' . $clientName . ' supprimé');

        return back()->with('success', 'Client supprimé avec succès');
    }
}
