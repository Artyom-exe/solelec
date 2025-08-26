<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Services\HtmlSanitizer;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

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

        return response()->json(['message' => 'Email envoyé avec succès'], 200);
    }
}
