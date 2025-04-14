<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Accueil');
})->name('accueil');

Route::get('services-portfolio', function (Request $request) {
    return Inertia::render('ServicesPortfolio', [
        'serviceId' => $request->input('serviceId')
    ]);
})->name('services-portfolio');

Route::get('services', [ServiceController::class, 'index']);

Route::get('portfolios', [PortfolioController::class, 'index']);
Route::get('portfolios/{portfolio}', [PortfolioController::class, 'show']);

Route::get('tags', [TagController::class, 'index']);
Route::get('tags/{tag}', [TagController::class, 'show']);

Route::get('faq', [FAQController::class, 'index']);

Route::post('send-email', [ContactController::class, 'sendEmail']);

Route::get('customer-reviews', [CustomerReviewController::class, 'index']);

Route::post('/quotes', [QuoteController::class, 'store']);


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Routes pour les clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Routes pour les interventions
    Route::get('/interventions', [InterventionController::class, 'index'])->name('interventions');
    Route::get('/interventions/create', [InterventionController::class, 'create'])->name('interventions.create');
    Route::post('/interventions', [InterventionController::class, 'store'])->name('interventions.store');
    Route::get('/interventions/{intervention}', [InterventionController::class, 'show'])->name('interventions.show');
    Route::get('/interventions/{intervention}/edit', [InterventionController::class, 'edit'])->name('interventions.edit');
    Route::put('/interventions/{intervention}', [InterventionController::class, 'update'])->name('interventions.update');
    Route::delete('/interventions/{intervention}', [InterventionController::class, 'destroy'])->name('interventions.destroy');

    // Routes pour les devis
    Route::get('/devis', [QuoteController::class, 'index'])->name('devis');
    Route::get('/devis/{quote}', [QuoteController::class, 'show'])->name('devis.show');
    Route::delete('/devis/{quote}', [QuoteController::class, 'destroy'])->name('devis.destroy');
});

Route::match(['get', 'post'], '/register', function () {
    return redirect('/login');
});
