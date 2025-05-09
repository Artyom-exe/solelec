<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Service;
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
        $interventions = Intervention::with(['client', 'devis', "devis.services"])->latest()->get();
        $clients = Client::select('id', 'name', 'lastname', 'email', 'phone')->get();
        $quotes = Quote::select('id', 'description', 'client_id')->get();
        $services = Service::select('id', 'title', 'short_description')->get();

        return Inertia::render('Admin/Interventions/Index', [
            'interventions' => $interventions,
            'clients' => $clients,
            'quotes' => $quotes,
            'services' => $services
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
        // Valider les données de base
        $request->validate([
            'status' => 'required|string|in:planifiée,en cours,terminée,annulée',
            'services' => 'required|array',
            'services.*' => 'exists:services,id'
        ]);

        // Gérer le client (existant ou nouveau)
        $clientId = null;
        $clientName = '';

        if ($request->has('clients_id') && !empty($request->clients_id)) {
            // Client existant
            $clientId = $request->clients_id;
            $client = Client::find($clientId);
            $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $clientId;
        } elseif ($request->has('new_client_name') && !empty($request->new_client_name)) {
            // Création d'un nouveau client
            $client = Client::create([
                'name' => $request->new_client_name,
                'lastname' => $request->new_client_lastname ?? '',
                'phone' => $request->new_client_phone ?? null,
                'email' => $request->new_client_email ?? null,
            ]);

            $clientId = $client->id;
            $clientName = $client->name . ' ' . $client->lastname;

            // Journalisation de la création du client
            ActivityLogger::log('client', $client, 'Nouveau client créé lors de la création d\'une intervention');
        } else {
            return redirect()->back()->with('error', 'Vous devez sélectionner un client ou en créer un nouveau');
        }

        // 1. Créer un devis avec statut 'converti'
        $quote = Quote::create([
            'client_id' => $clientId,
            'description' => 'Devis créé automatiquement pour intervention',
            'requested_date' => now(),
            'status' => 'converti'
        ]);

        // Associer les services au devis
        $quote->services()->attach($request->services);

        // Journalisation de la création du devis
        ActivityLogger::log('quote', $quote, 'Devis #' . $quote->id . ' créé automatiquement pour ' . $clientName);

        // 2. Créer l'intervention avec le devis créé
        $intervention = Intervention::create([
            'status' => $request->status,
            'date' => now(), // Date actuelle
            'notes' => '',
            'clients_id' => $clientId,
            'devis_id' => $quote->id
        ]);

        // Journalisation de l'activité
        ActivityLogger::log('intervention', $intervention, 'Nouvelle intervention ' . $request->status . ' créée pour ' . $clientName);

        return redirect()->route('interventions')->with('success', 'Intervention créée avec succès');
    }

    /**
     * Affiche les détails d'une intervention
     */
    public function show(Intervention $intervention)
    {
        $intervention->load(['client', 'devis', 'devis.services', 'images', 'notes.user']);

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

        // Préparer les données à mettre à jour
        $updateData = [
            'status' => $validated['status'],
            'date' => $validated['date'],
            'clients_id' => $validated['clients_id'],
            'devis_id' => $validated['devis_id'],
        ];

        // Ajouter les notes seulement si elles sont présentes dans la requête
        if (array_key_exists('notes', $validated)) {
            $updateData['notes'] = $validated['notes'];
        }

        // Mise à jour de l'intervention
        $intervention->update($updateData);

        // Suppression des images si demandé
        if (isset($validated['delete_images'])) {
            $imagesToDelete = InterventionImage::whereIn('id', $validated['delete_images'])->get();

            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->url_image);
                $image->delete();
            }
        }

        // Ajout de nouvelles images si présentes
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('interventions', 'public');

                InterventionImage::create([
                    'intervention_id' => $intervention->id,
                    'url_image' => $path
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
     * Met à jour uniquement le statut d'une intervention
     */
    public function updateStatus(Request $request, Intervention $intervention)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:planifiée,en cours,terminée,annulée',
        ]);

        $oldStatus = $intervention->status;

        // Mise à jour du statut de l'intervention
        $intervention->update([
            'status' => $validated['status'],
        ]);

        // Récupérer le client pour le message
        $client = $intervention->client;
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $intervention->clients_id;

        // Journalisation de l'activité
        ActivityLogger::log('intervention', $intervention, 'Statut de l\'intervention pour ' . $clientName . ' mis à jour: ' . $oldStatus . ' → ' . $validated['status']);

        return redirect()->back()->with('success', 'Statut de l\'intervention mis à jour avec succès');
    }
    
    /**
     * Met à jour uniquement la date d'une intervention
     */
    public function updateDate(Request $request, Intervention $intervention)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        // Enregistrer l'ancienne date pour le message de log
        $oldDate = $intervention->date;

        // Mise à jour de la date de l'intervention
        $result = $intervention->update([
            'date' => $validated['date'],
        ]);

        // Récupérer le client pour le message
        $client = $intervention->client;
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $intervention->clients_id;

        // Journalisation de l'activité
        ActivityLogger::log('intervention', $intervention, 'Date de l\'intervention pour ' . $clientName . ' mise à jour: ' . $oldDate . ' → ' . $validated['date']);

        return redirect()->back()->with('success', 'Date de l\'intervention mise à jour avec succès');
    }

    /**
     * Télécharge des images pour une intervention
     */
    public function uploadImages(Request $request, Intervention $intervention)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('interventions', 'public');

                $interventionImage = InterventionImage::create([
                    'intervention_id' => $intervention->id,
                    'url_image' => $path
                ]);

                $uploadedImages[] = [
                    'id' => $interventionImage->id,
                    'url_image' => $path,
                    'path' => $path,
                    'url' => Storage::url($path),
                    'intervention_id' => $intervention->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Récupérer le client pour le message de log
        $client = $intervention->client;
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $intervention->clients_id;

        // Journalisation de l'activité
        ActivityLogger::log('intervention', $intervention, count($uploadedImages) . ' image(s) ajoutée(s) à l\'intervention pour ' . $clientName);

        // Stocker les données des images dans la session flash pour qu'elles soient disponibles dans la réponse
        return back()->with('success', 'Photos ajoutées avec succès')->with('data', [
            'success' => true,
            'images' => $uploadedImages
        ]);
    }

    /**
     * Supprime une image d'intervention
     */
    public function deleteImage(InterventionImage $image)
    {
        // Récupérer l'intervention associée pour la journalisation
        $intervention = $image->intervention;
        $client = $intervention->client;
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $intervention->clients_id;

        // Supprimer le fichier du stockage
        Storage::disk('public')->delete($image->url_image);

        // Supprimer l'enregistrement de la base de données
        $image->delete();

        // Journalisation de l'activité
        ActivityLogger::log('intervention', $intervention, 'Image supprimée de l\'intervention pour ' . $clientName);

        return back()->with('success', 'Image supprimée avec succès');
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
            Storage::disk('public')->delete($image->url_image);
            $image->delete();
        }

        // Récupérer le devis associé avant de supprimer l'intervention
        $devis = $intervention->devis;

        $intervention->delete();

        // Supprimer le devis associé s'il existe
        if ($devis) {
            $devis->delete();
            // Journalisation de la suppression du devis
            ActivityLogger::log('delete', $devis, 'Devis associé à l\'intervention pour ' . $clientName . ' supprimé');
        }

        // Journalisation de l'activité
        ActivityLogger::log('delete', $intervention, 'Intervention ' . $interventionStatus . ' pour ' . $clientName . ' supprimée');

        return redirect()->route('interventions')->with('success', 'Intervention et devis associé supprimés avec succès');
    }

    /**
     * Convertit un devis en intervention
     */
    public function convertToIntervention(Quote $quote)
    {
        // Vérifier si le devis est déjà lié à une intervention
        if ($quote->interventions()->count() > 0) {
            return redirect()->route('devis')->with('error', 'Ce devis a déjà été converti en intervention.');
        }

        // Créer une nouvelle intervention basée sur le devis
        $intervention = new Intervention([
            'status' => 'planifiée',
            'date' => now()->addDays(3), // Par défaut: rdv dans 3 jours
            'notes' => '',
            'clients_id' => $quote->client_id,
            'devis_id' => $quote->id,
        ]);

        $intervention->save();

        // Changer le statut du devis en "converti"
        $quote->status = 'converti';
        $quote->save();

        // Récupérer le client pour le message
        $client = Client::find($quote->client_id);
        $clientName = $client ? $client->name . ' ' . $client->lastname : 'client #' . $quote->client_id;

        // Journalisation de l'activité
        ActivityLogger::log('quote', $quote, 'Devis #' . $quote->id . ' converti en intervention');

        return redirect()->route('devis')->with('success', 'Le devis a été converti en intervention avec succès.');
    }
}
