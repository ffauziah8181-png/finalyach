<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nama_target', 'kategori_icon', 'foto_sampul',
        'nominal_target', 'nominal_terkumpul', 'tanggal_target',
        'nabung_otomatis', 'nominal_otomatis', 'frekuensi_otomatis',
        'waktu_otomatis', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_target' => 'date',
            'nabung_otomatis' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function riwayat()
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    public function getPersentaseAttribute(): float
    {
        if ((float) $this->nominal_target <= 0) {
            return 0;
        }

        return round(((float) $this->nominal_terkumpul / (float) $this->nominal_target) * 100, 1);
    }
}
