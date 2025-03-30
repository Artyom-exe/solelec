<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerReviewController;

Route::get('/', function () {
    return Inertia::render('Accueil');
})->name('accueil');

Route::get('services', [ServiceController::class, 'index']);

Route::get('portfolios', [PortfolioController::class, 'index']);
Route::get('portfolios/{portfolio}', [PortfolioController::class, 'show']);

Route::get('tags', [TagController::class, 'index']);
Route::get('tags/{tag}', [TagController::class, 'show']);

Route::get('faq', [FAQController::class, 'index']);

Route::post('send-email', [ContactController::class, 'sendEmail']);

Route::get('customer-reviews', [CustomerReviewController::class, 'index']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
