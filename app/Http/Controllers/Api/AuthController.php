<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Daftar akun baru + kirim kode OTP verifikasi.
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_ktp' => $data['no_ktp'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        UserSetting::create(['user_id' => $user->id]);

        $this->kirimOtp($user);

        return response()->json([
            'message' => 'Registrasi berhasil. Kode verifikasi telah dikirim ke email Anda.',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Verifikasi akun menggunakan kode OTP 6 digit.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (! $user->otp_code || $user->otp_code !== $request->otp) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 422);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.'], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Verifikasi berhasil.',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Kirim ulang kode OTP (jika kedaluwarsa/belum masuk).
     */
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Akun sudah terverifikasi.'], 422);
        }

        $this->kirimOtp($user);

        return response()->json(['message' => 'Kode verifikasi baru telah dikirim.']);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if (! $user->email_verified_at) {
            return response()->json(['message' => 'Akun belum diverifikasi. Silakan cek email Anda.'], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Login cepat menggunakan PIN (setelah pernah login normal & set PIN).
     */
    public function loginWithPin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'pin' => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->pin || ! Hash::check($request->pin, $user->pin)) {
            return response()->json(['message' => 'PIN salah.'], 401);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    private function kirimOtp(User $user): void
    {
        $otp = (string) random_int(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Kirim email OTP. Buat App\Mail\OtpVerificationMail sesuai kebutuhan.
        try {
            Mail::raw("Kode verifikasi Keuanganku Anda: {$otp} (berlaku 10 menit)", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode Verifikasi Akun Keuanganku');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
