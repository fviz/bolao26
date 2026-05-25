<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileAvatarRequest;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Perfil atualizado.')]);

        return to_route('profile.edit');
    }

    /**
     * Store the user's profile avatar.
     */
    public function storeAvatar(ProfileAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->deleteAvatarFile();

        $extension = $request->file('avatar')->extension();
        $path = $request->file('avatar')->storeAs(
            "avatars/{$user->id}",
            Str::uuid().'.'.$extension,
            'public',
        );

        $user->avatar_path = $path;
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Foto de perfil atualizada.')]);

        return to_route('profile.edit');
    }

    /**
     * Remove the user's profile avatar.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $request->user()->deleteAvatarFile();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Foto de perfil removida.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->deleteAvatarFile();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
