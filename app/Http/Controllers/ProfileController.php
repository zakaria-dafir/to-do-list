<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        return response()->json(auth()->user());
    }

    public function update(Request $request)
    {
        $user = auth()->user();



        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500'
        ]);

        $updateData = [
            'full_name' => $request->name,  // DB utilise full_name
            'email' => $request->email,
            'phone_number' => $request->phone,  // DB utilise phone_number
            'address' => $request->bio  // DB utilise address
        ];

        $user->update($updateData);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect'
            ], 422);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Mot de passe modifié avec succès'
        ]);
    }

    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB max
        ]);

        $user = auth()->user();

        // Supprimer l'ancienne image si elle existe
        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        // Stocker la nouvelle image
        $imagePath = $request->file('profile_image')->store('profile_images', 'public');

        // Mettre à jour l'utilisateur - DB utilise 'image'
        $user->update([
            'image' => $imagePath,
        ]);

        // Recharger l'utilisateur à jour
        $user->refresh();

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès',
            'image' => $imagePath,
            'user' => $user
        ]);
    }
}
