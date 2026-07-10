<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (! isset($data['category_id']) && isset($data['categoryId'])) {
            $data['category_id'] = $data['categoryId'];
        }

        if (! isset($data['tipe']) && isset($data['type'])) {
            $normalizedType = match (strtolower((string) $data['type'])) {
                'expense', 'pengeluaran', 'out', 'keluar' => 'pengeluaran',
                'income', 'pemasukan', 'in', 'masuk' => 'pemasukan',
                default => $data['type'],
            };

            $data['tipe'] = $normalizedType;
        }

        if (! isset($data['jumlah']) && isset($data['amount'])) {
            $data['jumlah'] = $data['amount'];
        }

        if (! isset($data['catatan']) && isset($data['note'])) {
            $data['catatan'] = $data['note'];
        }

        if (! isset($data['tanggal']) && isset($data['date'])) {
            $data['tanggal'] = $data['date'];
        }

        if (! isset($data['bukti_transaksi']) && isset($data['bukti'])) {
            $data['bukti_transaksi'] = $data['bukti'];
        }

        $this->replace($data);
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
