<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_target' => $this->nama_target,
            'kategori_icon' => $this->kategori_icon,
            'foto_sampul' => $this->foto_sampul ? asset('storage/'.$this->foto_sampul) : null,
            'nominal_target' => (float) $this->nominal_target,
            'nominal_terkumpul' => (float) $this->nominal_terkumpul,
            'persentase' => $this->persentase,
            'tanggal_target' => $this->tanggal_target?->format('Y-m-d'),
            'nabung_otomatis' => $this->nabung_otomatis,
            'nominal_otomatis' => $this->nominal_otomatis ? (float) $this->nominal_otomatis : null,
            'frekuensi_otomatis' => $this->frekuensi_otomatis,
            'waktu_otomatis' => $this->waktu_otomatis,
            'status' => $this->status,
            'riwayat' => $this->whenLoaded('riwayat', function () {
                return $this->riwayat->map(fn ($r) => [
                    'id' => $r->id,
                    'tipe' => $r->tipe,
                    'jumlah' => (float) $r->jumlah,
                    'catatan' => $r->catatan,
                    'tanggal' => $r->created_at->format('d M Y'),
                ]);
            }),
        ];
    }
}
