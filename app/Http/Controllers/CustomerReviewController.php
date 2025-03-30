<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerReview;

class CustomerReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerReview::query();

        if ($request->has('stars')) {
            $query->where('note', $request->input('stars'));
        }

        // On retourne juste les infos utiles pour le frontend
        return $query->latest('date_publication')->get(['id', 'author', 'note', 'comment']);
    }
}
