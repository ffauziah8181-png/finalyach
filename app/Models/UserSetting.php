<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'dark_mode', 'bahasa', 'notif_transaksi_masuk',
        'notif_transaksi_keluar', 'notif_promo_info', 'login_biometrik',
    ];

    protected function casts(): array
    {
        return [
            'dark_mode' => 'boolean',
            'notif_transaksi_masuk' => 'boolean',
            'notif_transaksi_keluar' => 'boolean',
            'notif_promo_info' => 'boolean',
            'login_biometrik' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
