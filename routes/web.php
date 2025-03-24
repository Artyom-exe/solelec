<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TagController;

Route::get('/', function () {
    return Inertia::render('Accueil');
})->name('accueil');

Route::get('api/services', [ServiceController::class, 'index']);

Route::get('portfolios', [PortfolioController::class, 'index']);
Route::get('portfolios/{portfolio}', [PortfolioController::class, 'show']);

Route::get('tags', [TagController::class, 'index']);
Route::get('tags/{tag}', [TagController::class, 'show']);
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
