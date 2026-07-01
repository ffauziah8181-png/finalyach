<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user()->setting);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'dark_mode' => ['sometimes', 'boolean'],
            'bahasa' => ['sometimes', 'in:id,en'],
            'notif_transaksi_masuk' => ['sometimes', 'boolean'],
            'notif_transaksi_keluar' => ['sometimes', 'boolean'],
            'notif_promo_info' => ['sometimes', 'boolean'],
            'login_biometrik' => ['sometimes', 'boolean'],
        ]);

        $setting = $request->user()->setting()->firstOrCreate([]);
        $setting->update($data);

        if ($request->has('login_biometrik')) {
            $request->user()->update(['biometric_enabled' => $data['login_biometrik']]);
        }

        return response()->json(['message' => 'Pengaturan berhasil disimpan.', 'data' => $setting]);
    }
}
