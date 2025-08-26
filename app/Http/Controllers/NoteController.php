<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Intervention;
use App\Http\Requests\NoteRequest;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use HTMLPurifier;

class NoteController extends Controller
{
    /**
     * Store a newly created note in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Intervention  $intervention
     * @return \Illuminate\Http\Response
     */
    public function store(NoteRequest $request, Intervention $intervention, HtmlSanitizer $sanitizer)
    {
        $data = $request->validated();

        $content = $sanitizer->sanitize($data['content'] ?? '');

        $note = new Note([
            'content' => $content,
            'user_id' => Auth::id(),
        ]);

        $intervention->notes()->save($note);

        // Recharger l'intervention avec les notes pour retourner les données à jour
        $intervention->load('notes.user');

        return back()->with('success', 'Note ajoutée avec succès');
    }

    /**
     * Remove the specified note from storage.
     *
     * @param  \App\Models\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        // Pour simplifier, on permet à tout utilisateur authentifié de supprimer n'importe quelle note
        // Dans un environnement de production, vous voudriez peut-être restreindre cela davantage

        $note->delete();

        return back()->with('success', 'Note supprimée avec succès');
    }
}
