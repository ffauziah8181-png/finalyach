<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function paginateForUser(int $userId, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Transaction::with('category')->where('user_id', $userId);

        if (! empty($filters['tipe']) && in_array($filters['tipe'], ['pengeluaran', 'pemasukan'])) {
            $query->where('tipe', $filters['tipe']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['dari'])) {
            $query->whereDate('tanggal', '>=', $filters['dari']);
        }

        if (! empty($filters['sampai'])) {
            $query->whereDate('tanggal', '<=', $filters['sampai']);
        }

        if (! empty($filters['cari'])) {
            $query->where('catatan', 'like', '%'.$filters['cari'].'%');
        }

        return $query->orderByDesc('tanggal')->orderByDesc('id')->paginate($perPage);
    }

    public function findForUser(int $id, int $userId): ?Transaction
    {
        return Transaction::with('category')->where('user_id', $userId)->find($id);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data)->load('category');
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh('category');
    }

    public function delete(Transaction $transaction): bool
    {
        return (bool) $transaction->delete();
    }

    public function totalByType(int $userId, string $tipe, ?string $dari = null, ?string $sampai = null): float
    {
        $query = Transaction::where('user_id', $userId)->where('tipe', $tipe);

        if ($dari && $sampai) {
            $query->whereBetween('tanggal', [$dari, $sampai]);
        }

        return (float) $query->sum('jumlah');
    }

    public function latestForUser(int $userId, int $limit = 5): Collection
    {
        return Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->limit($limit)->get();
    }

    public function topCategoriesByExpense(int $userId, int $bulan, int $tahun, int $limit = 3): Collection
    {
        return Transaction::with('category')
            ->where('user_id', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->select('category_id')
            ->selectRaw('SUM(jumlah) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }
}
