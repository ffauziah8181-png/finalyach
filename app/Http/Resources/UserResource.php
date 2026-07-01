<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'no_ktp' => $this->no_ktp,
            'avatar' => $this->avatar,
            'biometric_enabled' => $this->biometric_enabled,
            'email_verified_at' => $this->email_verified_at,
            'saldo' => $this->saldo,
        ];
    }
}
