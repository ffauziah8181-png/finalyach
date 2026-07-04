<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        return $this->success('Berhasil mengambil profil.', new UserResource($request->user()));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['sometimes', 'nullable', 'string', 'max:20'],
            'avatar' => ['sometimes', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatar', 'public');
        }

        $user->update($data);

        return $this->success('Profil berhasil diperbarui.', new UserResource($user));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password_lama' => ['required'],
            'password_baru' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            return $this->error('Password lama salah.', null, 422);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return $this->success('Password berhasil diubah.');
    }

    public function changePin(Request $request)
    {
        $request->validate(['pin' => ['required', 'digits:6']]);

        $request->user()->update(['pin' => Hash::make($request->pin)]);

        return $this->success('PIN berhasil diperbarui.');
    }
}
