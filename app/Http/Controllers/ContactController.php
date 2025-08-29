<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Services\HtmlSanitizer;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function sendEmail(ContactRequest $request)
    {
        $data = $request->validated();

        $sanitizer = app(HtmlSanitizer::class);
        if (!empty($data['message'] ?? null)) {
            $data['message'] = $sanitizer->sanitize($data['message']);
        }

        // Envoi de l'email à l'adresse souhaitée
        Mail::to('solelec.lmbt@gmail.com')->send(new ContactFormMail($data));

        // Journaliser le consentement au traitement des données (preuve minimale)
        try {
            Log::info('Contact form submitted', [
                'email' => $data['email'] ?? null,
                'accepted_privacy' => isset($data['acceptConditions']) ? (bool)$data['acceptConditions'] : false,
                'accepted_at' => isset($data['acceptConditions']) ? now()->toDateTimeString() : null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Ne pas empêcher l'envoi si le logging échoue
        }

        return response()->json(['message' => 'Email envoyé avec succès'], 200);
    }
}
