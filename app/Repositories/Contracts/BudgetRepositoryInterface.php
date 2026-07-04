<?php

namespace App\Repositories\Contracts;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Collection;

interface BudgetRepositoryInterface
{
    public function forUserPeriod(int $userId, int $bulan, int $tahun): Collection;

    public function findForUser(int $id, int $userId): ?Budget;

    public function upsert(array $attributes, array $values): Budget;

    public function delete(Budget $budget): bool;
}
