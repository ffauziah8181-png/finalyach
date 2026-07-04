<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    public function __construct(
        protected TransactionRepositoryInterface $transactions
    ) {}

    public function listForUser(int $userId, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->transactions->paginateForUser($userId, $filters, $perPage);
    }

    public function create(int $userId, array $data, ?UploadedFile $bukti = null): Transaction
    {
        $data['user_id'] = $userId;

        if ($bukti) {
            $data['bukti_transaksi'] = $bukti->store('transaksi', 'public');
        }

        return $this->transactions->create($data);
    }

    public function update(Transaction $transaction, array $data, ?UploadedFile $bukti = null): Transaction
    {
        if ($bukti) {
            if ($transaction->bukti_transaksi) {
                Storage::disk('public')->delete($transaction->bukti_transaksi);
            }
            $data['bukti_transaksi'] = $bukti->store('transaksi', 'public');
        }

        return $this->transactions->update($transaction, $data);
    }

    public function delete(Transaction $transaction): bool
    {
        if ($transaction->bukti_transaksi) {
            Storage::disk('public')->delete($transaction->bukti_transaksi);
        }

        return $this->transactions->delete($transaction);
    }
}
