<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(protected TransactionService $transactionService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['tipe', 'category_id', 'dari', 'sampai', 'cari']);

        $transactions = $this->transactionService->listForUser(
            $request->user()->id,
            $filters,
            (int) $request->get('per_page', 20)
        );

        return TransactionResource::collection($transactions)
            ->additional(['success' => true, 'message' => 'Berhasil mengambil daftar transaksi.']);
    }

    public function store(TransactionRequest $request)
    {
        $transaction = $this->transactionService->create(
            $request->user()->id,
            $request->validated(),
            $request->file('bukti_transaksi')
        );

        return $this->created('Transaksi berhasil disimpan.', new TransactionResource($transaction));
    }

    public function show(Request $request, Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return $this->success('Berhasil mengambil detail transaksi.', new TransactionResource($transaction->load('category')));
    }

    public function update(TransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $updated = $this->transactionService->update(
            $transaction,
            $request->validated(),
            $request->file('bukti_transaksi')
        );

        return $this->success('Transaksi berhasil diperbarui.', new TransactionResource($updated));
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $this->transactionService->delete($transaction);

        return $this->success('Transaksi berhasil dihapus.');
    }
}
