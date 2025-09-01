<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Exclure SEULEMENT les routes d'authentification admin du CSRF
        // Les formulaires publics (contact, devis) restent protégés
        $middleware->validateCsrfTokens(except: [
            '/login',
            '/logout',
            '/admin/profile*',  // Routes de profil admin
            '/admin/*/password', // Changements de mot de passe
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
