<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    public function paginateForUser(int $userId, array $filters, int $perPage = 20): LengthAwarePaginator;

    public function findForUser(int $id, int $userId): ?Transaction;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): Transaction;

    public function delete(Transaction $transaction): bool;

    public function totalByType(int $userId, string $tipe, ?string $dari = null, ?string $sampai = null): float;

    public function latestForUser(int $userId, int $limit = 5): Collection;

    public function topCategoriesByExpense(int $userId, int $bulan, int $tahun, int $limit = 3): Collection;
}
