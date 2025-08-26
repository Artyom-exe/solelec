<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminProfileController extends Controller
{
    public function show()
    {
        return Inertia::render('AdminShow', [
            'user' => auth()->user()
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $data = $request->validated();

        auth()->user()->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return back()->with('success', 'Informations du profil mises à jour avec succès');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $data = $request->validated();

        auth()->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Mot de passe mis à jour avec succès');
    }
}
