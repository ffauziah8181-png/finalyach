<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSetting;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $users
    ) {}

    /**
     * Daftar akun baru, buat setting default, kirim OTP verifikasi.
     */
    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_ktp' => $data['no_ktp'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        UserSetting::create(['user_id' => $user->id]);

        $this->generateAndSendOtp($user);

        return $user;
    }

    /**
     * @throws Exception jika OTP salah atau kedaluwarsa
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user) {
            throw new Exception('Akun tidak ditemukan.', 404);
        }

        if (! $user->otp_code || $user->otp_code !== $otp) {
            throw new Exception('Kode verifikasi salah.', 422);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            throw new Exception('Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.', 422);
        }

        $this->users->update($user, [
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return ['user' => $user->fresh(), 'token' => $token];
    }

    /**
     * @throws Exception
     */
    public function resendOtp(string $email): void
    {
        $user = $this->users->findByEmail($email);

        if (! $user) {
            throw new Exception('Akun tidak ditemukan.', 404);
        }

        if ($user->email_verified_at) {
            throw new Exception('Akun sudah terverifikasi.', 422);
        }

        $this->generateAndSendOtp($user);
    }

    /**
     * @throws Exception
     */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new Exception('Email atau password salah.', 401);
        }

        // Sementara nonaktifkan validasi verifikasi email agar login bisa langsung lewat.
        // Hapus komentar ini kembali setelah akun sudah siap diverifikasi.
        // if (! $user->email_verified_at) {
        //     throw new Exception('Akun belum diverifikasi. Silakan cek email Anda.', 403);
        // }

        $token = $user->createToken('mobile')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * @throws Exception
     */
    public function loginWithPin(string $email, string $pin): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! $user->pin || ! Hash::check($pin, $user->pin)) {
            throw new Exception('PIN salah.', 401);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    private function generateAndSendOtp(User $user): void
    {
        $otp = (string) random_int(100000, 999999);

        $this->users->update($user, [
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::raw("Kode verifikasi Keuanganku Anda: {$otp} (berlaku 10 menit)", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode Verifikasi Akun Keuanganku');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
