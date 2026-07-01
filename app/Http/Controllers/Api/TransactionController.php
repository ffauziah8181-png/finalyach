<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * List transaksi milik user, mendukung filter tipe, kategori, rentang tanggal, dan pencarian catatan.
     */
    public function index(Request $request)
    {
        $query = Transaction::with('category')
            ->where('user_id', $request->user()->id);

        if ($request->filled('tipe') && in_array($request->tipe, ['pengeluaran', 'pemasukan'])) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        if ($request->filled('cari')) {
            $query->where('catatan', 'like', '%'.$request->cari.'%');
        }

        $transactions = $query->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate($request->get('per_page', 20));

        return TransactionResource::collection($transactions);
    }

    public function store(TransactionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('bukti_transaksi')) {
            $data['bukti_transaksi'] = $request->file('bukti_transaksi')->store('transaksi', 'public');
        }

        $transaction = Transaction::create($data);

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'data' => new TransactionResource($transaction->load('category')),
        ], 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        $this->pastikanMilikUser($request, $transaction);

        return new TransactionResource($transaction->load('category'));
    }

    public function update(TransactionRequest $request, Transaction $transaction)
    {
        $this->pastikanMilikUser($request, $transaction);

        $data = $request->validated();

        if ($request->hasFile('bukti_transaksi')) {
            $data['bukti_transaksi'] = $request->file('bukti_transaksi')->store('transaksi', 'public');
        }

        $transaction->update($data);

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => new TransactionResource($transaction->load('category')),
        ]);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $this->pastikanMilikUser($request, $transaction);
        $transaction->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }

    private function pastikanMilikUser(Request $request, Transaction $transaction): void
    {
        abort_if($transaction->user_id !== $request->user()->id, 403, 'Tidak diizinkan.');
    }
}
