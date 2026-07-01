<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['sometimes', 'nullable', 'string', 'max:20'],
            'avatar' => ['sometimes', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatar', 'public');
        }

        $user->update($data);

        return response()->json(['message' => 'Profil berhasil diperbarui.', 'data' => new UserResource($user)]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password_lama' => ['required'],
            'password_baru' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    public function changePin(Request $request)
    {
        $request->validate(['pin' => ['required', 'digits:6']]);

        $request->user()->update(['pin' => Hash::make($request->pin)]);

        return response()->json(['message' => 'PIN berhasil diperbarui.']);
    }
}
