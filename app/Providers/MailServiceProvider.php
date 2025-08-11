<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Personnaliser le template de réinitialisation de mot de passe
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            // Forcer l'utilisation de notre template personnalisé
            $message = new MailMessage();
            $message->subject('Réinitialisation de votre mot de passe - SOLELEC');
            $message->view('emails.reset-password', [
                'actionUrl' => $url,
                'user' => $notifiable,
                'token' => $token,
            ]);

            return $message;
        });
    }
}
