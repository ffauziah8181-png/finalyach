<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_target' => ['required', 'string', 'max:255'],
            'kategori_icon' => ['nullable', 'string', 'max:50'],
            'foto_sampul' => ['nullable', 'image', 'max:4096'],
            'nominal_target' => ['required', 'numeric', 'min:1'],
            'tanggal_target' => ['nullable', 'date', 'after:today'],
            'nabung_otomatis' => ['boolean'],
            'nominal_otomatis' => ['nullable', 'numeric', 'min:0', 'required_if:nabung_otomatis,true'],
            'frekuensi_otomatis' => ['nullable', 'in:harian,mingguan,bulanan', 'required_if:nabung_otomatis,true'],
            'waktu_otomatis' => ['nullable', 'date_format:H:i'],
        ];
    }
}
