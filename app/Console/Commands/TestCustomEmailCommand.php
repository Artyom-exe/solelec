<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestCustomEmailCommand extends Command
{
    protected $signature = 'email:test-custom {email}';
    protected $description = 'Test notre template d\'email personnalisé';

    public function handle()
    {
        $email = $this->argument('email');

        try {
            Mail::send('emails.reset-password', [
                'actionUrl' => 'http://solelec.test/reset-password/test-token?email=' . urlencode($email),
            ], function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test - Réinitialisation de votre mot de passe - SOLELEC');
            });

            $this->info("Email de test envoyé avec succès à : {$email}");
            $this->info("Vérifiez votre boîte mail ou Mailtrap !");

        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi : " . $e->getMessage());
        }
    }
}
