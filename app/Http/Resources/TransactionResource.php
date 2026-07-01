<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipe' => $this->tipe,
            'jumlah' => (float) $this->jumlah,
            'catatan' => $this->catatan,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'bukti_transaksi' => $this->bukti_transaksi ? asset('storage/'.$this->bukti_transaksi) : null,
            'kategori' => [
                'id' => $this->category->id,
                'nama' => $this->category->nama,
                'icon' => $this->category->icon,
                'warna' => $this->category->warna,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
