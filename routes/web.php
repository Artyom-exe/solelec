<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

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

Route::get('faq', [FaqController::class, 'index']);

Route::post('send-email', [ContactController::class, 'sendEmail']);

Route::get('customer-reviews', [CustomerReviewController::class, 'index']);

Route::post('/quotes', [QuoteController::class, 'store']);

// Simple sitemap route (cached 1 hour). Generates basic sitemap with main pages and portfolios.


Route::get('/sitemap.xml', function () {
    $host = rtrim(config('app.url', env('APP_URL')), '/');

    $xml = new \SimpleXMLElement('<urlset/>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    $pages = [
        '/',
        '/services-portfolio',
    ];

    foreach ($pages as $p) {
        $url = $xml->addChild('url');
        $url->addChild('loc', $host . $p);
        $url->addChild('lastmod', now()->toAtomString()); // date ISO8601
        $url->addChild('changefreq', 'monthly');
        $url->addChild('priority', '0.8');
    }

    return response($xml->asXML(), 200)
        ->header('Content-Type', 'application/xml; charset=utf-8');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Route pour le profil admin
    Route::get('/profile', [App\Http\Controllers\AdminProfileController::class, 'show'])->name('profile.admin');
    Route::put('/profile', [App\Http\Controllers\AdminProfileController::class, 'updateProfile'])->name('profile.admin.update');
    Route::put('/profile/password', [App\Http\Controllers\AdminProfileController::class, 'updatePassword'])->name('profile.admin.password');

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
    Route::put('/interventions/{intervention}/status', [InterventionController::class, 'updateStatus'])->name('interventions.updateStatus');
    Route::put('/interventions/{intervention}/date', [InterventionController::class, 'updateDate'])->name('interventions.updateDate');
    Route::put('/interventions/{intervention}/services', [InterventionController::class, 'updateServices'])->name('interventions.services.update');
    Route::post('/interventions/{intervention}/upload-images', [InterventionController::class, 'uploadImages'])->name('interventions.upload-images');
    Route::delete('/interventions/images/{image}', [InterventionController::class, 'deleteImage'])->name('interventions.delete-image');
    Route::delete('/interventions/{intervention}', [InterventionController::class, 'destroy'])->name('interventions.destroy');
    Route::get('/devis/{quote}/convert', [InterventionController::class, 'convertToIntervention'])->name('devis.convert');

    // Routes pour les notes
    Route::post('/interventions/{intervention}/notes', [NoteController::class, 'store'])->name('interventions.notes.store');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Routes pour les devis
    Route::get('/devis', [QuoteController::class, 'index'])->name('devis');
    Route::delete('/devis/{quote}', [QuoteController::class, 'destroy'])->name('devis.destroy');

    // Routes pour les notifications
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::get('/notifications/count', [NotificationController::class, 'getNotificationCount'])->name('notifications.count');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification'])->name('notifications.test');
});

// Registration is disabled (single user). Redirect any /register requests to /login.
use Laravel\Fortify\Features;

Route::match(['get', 'post'], '/register', function () {
    // If registration is disabled, return 404 to match test expectations
    if (! Features::enabled(Features::registration())) {
        abort(404);
    }

    return redirect('/login');
});

// Politique de confidentialité (page statique)
Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Mentions légales
Route::get('/mentions-legales', function () {
    return view('mentions');
})->name('mentions');
