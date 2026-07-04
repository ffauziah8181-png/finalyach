<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
    'name', 'email', 'no_hp', 'no_ktp', 'password', 'avatar',
    'pin', 'biometric_enabled', 'otp_code', 'otp_expires_at',
    'email_verified_at',
];

    protected $hidden = [
        'password', 'remember_token', 'pin', 'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'biometric_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function setting()
    {
        return $this->hasOne(UserSetting::class);
    }

    // Saldo = total pemasukan - total pengeluaran
    public function getSaldoAttribute(): float
    {
        $masuk = $this->transactions()->where('tipe', 'pemasukan')->sum('jumlah');
        $keluar = $this->transactions()->where('tipe', 'pengeluaran')->sum('jumlah');

        return (float) $masuk - (float) $keluar;
    }
}
