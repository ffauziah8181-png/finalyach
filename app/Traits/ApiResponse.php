<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Menyeragamkan format response API di semua controller.
 *
 * Format sukses:
 * { "success": true, "message": "...", "data": {...} }
 *
 * Format gagal:
 * { "success": false, "message": "...", "errors": {...} }
 */
trait ApiResponse
{
    protected function success(string $message = 'Berhasil.', mixed $data = null, int $code = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $code);
    }

    protected function created(string $message = 'Data berhasil dibuat.', mixed $data = null): JsonResponse
    {
        return $this->success($message, $data, 201);
    }

    protected function error(string $message = 'Terjadi kesalahan.', mixed $errors = null, int $code = 400): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if (! is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    protected function notFound(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    protected function forbidden(string $message = 'Anda tidak diizinkan melakukan aksi ini.'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    protected function unauthorized(string $message = 'Silakan login terlebih dahulu.'): JsonResponse
    {
        return $this->error($message, null, 401);
    }
}
