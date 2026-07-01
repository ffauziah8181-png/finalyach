<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'tipe' => ['required', 'in:pengeluaran,pemasukan'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'bukti_transaksi' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
