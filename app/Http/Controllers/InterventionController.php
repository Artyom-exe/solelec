<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Client;
use App\Models\Quote;
use App\Models\InterventionImage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class InterventionController extends Controller
{
    /**
     * Affiche la liste des interventions
     */
    public function index()
    {
        $interventions = Intervention::with(['client', 'devis'])->latest()->get();

        return Inertia::render('Admin/Interventions/Index', [
            'interventions' => $interventions
        ]);
    }

    /**
     * Affiche le formulaire de création d'une intervention
     */
    public function create()
    {
        $clients = Client::select('id', 'name', 'lastname', 'email')->get();
        $quotes = Quote::select('id', 'description', 'client_id')->get();

        return Inertia::render('Admin/Interventions/Create', [
            'clients' => $clients,
            'quotes' => $quotes
        ]);
    }

    /**
     * Enregistre une nouvelle intervention dans la base de données
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:planifiée,en cours,terminée,annulée',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'clients_id' => 'required|exists:clients,id',
            'devis_id' => 'nullable|exists:quotes,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Création de l'intervention
        $intervention = Intervention::create($validated);

        // Traitement des images si présentes
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('interventions', 'public');

                InterventionImage::create([
                    'intervention_id' => $intervention->id,
                    'path' => $path
                ]);
            }
        }

        // Récupérer le client pour le message
        $client = Client::find($validated['clients_id']);
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $validated['clients_id'];

        // Journalisation de l'activité
        ActivityLogger::log('intervention', $intervention, 'Nouvelle intervention ' . $validated['status'] . ' créée pour ' . $clientName);

        return redirect()->route('interventions')->with('success', 'Intervention créée avec succès');
    }

    /**
     * Affiche les détails d'une intervention
     */
    public function show(Intervention $intervention)
    {
        $intervention->load(['client', 'devis', 'images']);

        return Inertia::render('Admin/Interventions/Show', [
            'intervention' => $intervention
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'une intervention
     */
    public function edit(Intervention $intervention)
    {
        $intervention->load('images');
        $clients = Client::select('id', 'name', 'lastname', 'email')->get();
        $quotes = Quote::select('id', 'description', 'client_id')->get();

        return Inertia::render('Admin/Interventions/Edit', [
            'intervention' => $intervention,
            'clients' => $clients,
            'quotes' => $quotes
        ]);
    }

    /**
     * Met à jour les informations d'une intervention
     */
    public function update(Request $request, Intervention $intervention)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:planifiée,en cours,terminée,annulée',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'clients_id' => 'required|exists:clients,id',
            'devis_id' => 'nullable|exists:quotes,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:intervention_images,id'
        ]);

        // Vérifier si le statut a changé pour le message de journalisation
        $statusChanged = $intervention->status !== $validated['status'];
        $oldStatus = $intervention->status;

        // Mise à jour de l'intervention
        $intervention->update([
            'status' => $validated['status'],
            'date' => $validated['date'],
            'notes' => $validated['notes'],
            'clients_id' => $validated['clients_id'],
            'devis_id' => $validated['devis_id'],
        ]);

        // Suppression des images si demandé
        if (isset($validated['delete_images'])) {
            $imagesToDelete = InterventionImage::whereIn('id', $validated['delete_images'])->get();

            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        // Ajout de nouvelles images si présentes
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('interventions', 'public');

                InterventionImage::create([
                    'intervention_id' => $intervention->id,
                    'path' => $path
                ]);
            }
        }

        // Récupérer le client pour le message
        $client = Client::find($validated['clients_id']);
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $validated['clients_id'];

        // Journalisation de l'activité
        if ($statusChanged) {
            ActivityLogger::log('intervention', $intervention, 'Intervention pour ' . $clientName . ' mise à jour: ' . $oldStatus . ' → ' . $validated['status']);
        } else {
            ActivityLogger::log('update', $intervention, 'Intervention #' . $intervention->id . ' mise à jour');
        }

        return redirect()->route('interventions')->with('success', 'Intervention mise à jour avec succès');
    }

    /**
     * Supprime une intervention
     */
    public function destroy(Intervention $intervention)
    {
        // Récupérer des informations pour la journalisation
        $client = $intervention->client;
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client inconnu';
        $interventionStatus = $intervention->status;

        // Suppression des images associées
        foreach ($intervention->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $intervention->delete();

        // Journalisation de l'activité
        ActivityLogger::log('delete', $intervention, 'Intervention ' . $interventionStatus . ' pour ' . $clientName . ' supprimée');

        return redirect()->route('interventions')->with('success', 'Intervention supprimée avec succès');
    }
}
