<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
            'acceptConditions' => 'required|accepted'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message
        ];

        // Envoi de l'email à l'adresse souhaitée
        Mail::to('solelec.lmbt@gmail.com')->send(new ContactFormMail($data));

        return response()->json(['message' => 'Email envoyé avec succès'], 200);
    }
}
