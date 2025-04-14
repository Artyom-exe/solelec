<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Quote;
use App\Models\Intervention;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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

        return Inertia::render('Admin/Dashboard', [
            'pendingQuotesCount' => $pendingQuotesCount,
            'ongoingInterventionsCount' => $ongoingInterventionsCount,
        ]);
    }
}
