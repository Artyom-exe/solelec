<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quote;
use App\Models\Service;
use App\Services\ActivityLogger;
use App\Http\Requests\QuoteStoreRequest;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Services\HtmlSanitizer;

class QuoteController extends Controller
{
    protected HtmlSanitizer $sanitizer;

    public function __construct(HtmlSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }
    /**
     * Affiche la liste des devis
     */
    public function index()
    {
        $quotes = Quote::with(['client', 'services'])
            ->where('status', '!=', 'converti')  // Exclusion des devis convertis
            ->latest()
            ->get();

        return Inertia::render('Admin/Quotes/Index', [
            'quotes' => $quotes
        ]);
    }

    /**
     * Affiche les détails d'un devis
     */
    public function show(Quote $quote)
    {
    }

    /**
     * Traite la création d'un nouveau devis depuis le formulaire public
     */
    public function store(QuoteStoreRequest $request)
    {
        // Récupérer les données validées via QuoteStoreRequest
        $data = $request->validated();

        // Assainir la description riche côté serveur (HTMLPurifier)
            if (!empty($data['description'] ?? null)) {
                $data['description'] = $this->sanitizer->sanitize($data['description']);
            }

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

            // Journalisation de la création du client
            ActivityLogger::log('create', $client, 'Client ' . $client->name . ' ' . $client->lastname . ' ajouté via formulaire de devis');
        }

        // 3) Créer le devis (quote) avec statut "pending"
        $quote = Quote::create([
            'client_id'      => $client->id,
            'description'    => $data['description'],
            'requested_date' => $data['requested_date'] ?? null,
            'end_date'       => $data['end_date'] ?? null,
            'status'         => 'pending',
        ]);

        // 4) Associer le(s) service(s) choisi(s)
        // $data['services'] est un tableau d'IDs => ex: [1, 3, 5]
        $quote->services()->attach($data['services']);

        // Journalisation de la création du devis
        ActivityLogger::log('quote', $quote, 'Nouveau devis reçu pour ' . $client->name . ' ' . $client->lastname);

        // 5) Retourner une réponse
        return response()->json([
            'message' => 'Devis créé avec succès !',
            'quote'   => $quote->load('client', 'services'),
        ], 201);
    }

    /**
     * Supprime un devis
     */
    public function destroy(Quote $quote)
    {
        // Suppression des relations avant de supprimer le devis
        $quote->services()->detach();

        // Vérifier s'il y a des interventions liées
        if ($quote->interventions()->count() > 0) {
            return redirect()->back()->with('error', 'Ce devis ne peut pas être supprimé car il possède des interventions associées.');
        }

        // Récupérer les informations du devis pour la journalisation
        $quoteInfo = 'Devis #' . $quote->id . ' pour ' . ($quote->client ? $quote->client->name . ' ' . $quote->client->lastname : 'client inconnu');

        $quote->delete();

        // Journalisation de la suppression du devis
        ActivityLogger::log('delete', $quote, $quoteInfo . ' supprimé');

        return redirect()->route('devis')->with('success', 'Devis supprimé avec succès');
    }
}
