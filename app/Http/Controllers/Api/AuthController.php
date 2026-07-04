<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return $this->created(
            'Registrasi berhasil. Kode verifikasi telah dikirim ke email Anda.',
            ['user' => new UserResource($user)]
        );
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        try {
            $result = $this->authService->verifyOtp($request->email, $request->otp);

            return $this->success('Verifikasi berhasil.', [
                'token' => $result['token'],
                'user' => new UserResource($result['user']),
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), null, $e->getCode() ?: 422);
        }
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $this->authService->resendOtp($request->email);

            return $this->success('Kode verifikasi baru telah dikirim.');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), null, $e->getCode() ?: 422);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->email, $request->password);

            return $this->success('Login berhasil.', [
                'token' => $result['token'],
                'user' => new UserResource($result['user']),
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), null, $e->getCode() ?: 401);
        }
    }

    public function loginWithPin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'pin' => ['required', 'digits:6'],
        ]);

        try {
            $result = $this->authService->loginWithPin($request->email, $request->pin);

            return $this->success('Login berhasil.', [
                'token' => $result['token'],
                'user' => new UserResource($result['user']),
            ]);
        } catch (Exception $e) {
            return $this->error($e->getMessage(), null, $e->getCode() ?: 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Berhasil keluar.');
    }

    public function me(Request $request)
    {
        return $this->success('Berhasil mengambil data user.', new UserResource($request->user()));
    }
}
