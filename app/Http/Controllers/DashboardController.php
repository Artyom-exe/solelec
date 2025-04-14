<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Intervention;
use App\Models\Activity;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord administrateur
     */
    public function index()
    {
        // Vérifier si l'utilisateur est administrateur
        if (auth()->id() !== 1) {
            abort(403);
        }

        // Compter les devis en attente
        $pendingQuotesCount = Quote::where('status', 'pending')->count();

        // Compter les interventions en cours
        $ongoingInterventionsCount = Intervention::where('status', 'en cours')->count();

        // Récupérer les 10 dernières activités
        $recentActivities = Activity::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'typeLabel' => $this->getTypeLabel($activity->type),
                    'description' => $activity->description,
                    'user' => $activity->user ? $activity->user->name : 'Système',
                    'timeAgo' => $activity->created_at->diffForHumans(),
                    'date' => $activity->created_at->format('d/m/Y H:i')
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'pendingQuotesCount' => $pendingQuotesCount,
            'ongoingInterventionsCount' => $ongoingInterventionsCount,
            'recentActivities' => $recentActivities
        ]);
    }

    /**
     * Retourne le libellé en français selon le type d'activité
     */
    private function getTypeLabel($type)
    {
        return [
            'create' => 'Création',
            'update' => 'Modification',
            'delete' => 'Suppression',
            'quote' => 'Devis',
            'intervention' => 'Intervention'
        ][$type] ?? ucfirst($type);
    }
}
