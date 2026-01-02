<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProfilePhotoController extends Controller
{
    /**
     * Atualiza a foto de perfil do usuário.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => [
                'required',
                File::image()
                    ->max(2048) // 2MB
                    ->dimensions(Rule::dimensions()->maxWidth(2000)->maxHeight(2000)),
            ],
        ]);

        $user = $request->user();

        // Deletar foto antiga se existir
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Salvar nova foto
        $path = $validated['photo']->store('profile-photos', 'public');

        $user->update([
            'profile_photo_path' => $path,
        ]);

        return back()->with('success', 'Foto de perfil atualizada com sucesso!');
    }

    /**
     * Remove a foto de perfil do usuário.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->update([
            'profile_photo_path' => null,
        ]);

        return back()->with('success', 'Foto de perfil removida com sucesso!');
    }
}

