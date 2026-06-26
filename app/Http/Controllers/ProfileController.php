<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\Auth\ProfileAccess;
use App\Support\Media\ImageUploadService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUpload,
        private readonly ProfileAccess $profileAccess,
    ) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->load('hajjParticipant');

        $props = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar_url' => $user->avatarUrl(),
                'avatar_initial' => $user->avatarInitial(),
            ],
            'canUpdateProfile' => $this->profileAccess->canUpdate($user),
            'mustVerifyEmail' => $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail(),
            'status' => session('status'),
        ];

        if ($user->hajjParticipant) {
            $participant = $user->hajjParticipant;

            return Inertia::render('Profile/PesertaEdit', [
                ...$props,
                'participant' => [
                    'tahun_haji' => $participant->tahun_haji,
                    'nomor_porsi' => $participant->nomor_porsi,
                    'nama' => $participant->nama,
                    'alamat' => $participant->alamat,
                    'desa' => $participant->desa,
                    'kecamatan' => $participant->kecamatan,
                    'telepon' => $participant->telepon,
                    'kloter' => $participant->kloter,
                    'rombongan' => $participant->rombongan,
                    'regu' => $participant->regu,
                ],
            ]);
        }

        return Inertia::render('Profile/Edit', $props);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->load('hajjParticipant');

        if ($request->boolean('remove_avatar')) {
            $this->imageUpload->deleteIfExists($user->avatar_path);
            $user->avatar_path = null;
        } elseif ($request->hasFile('avatar')) {
            $user->avatar_path = $this->imageUpload->store(
                $request->file('avatar'),
                'uploads/avatars',
                [
                    'max_width' => 256,
                    'max_height' => 256,
                    'quality' => 85,
                    'old_path' => $user->avatar_path,
                ],
            );
        }

        $user->fill($request->safe()->only(['name', 'username', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($participant = $user->hajjParticipant) {
            $participant->update($request->safe()->only(['telepon', 'alamat']));
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function export(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $user->roles->pluck('name')->values()->all(),
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ], 200, [
            'Content-Disposition' => 'attachment; filename="profile-export-'.$user->id.'.json"',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $this->imageUpload->deleteIfExists($user->avatar_path);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
