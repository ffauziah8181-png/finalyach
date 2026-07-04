<?php

namespace App\Repositories\Eloquent;

use App\Models\Budget;
use App\Repositories\Contracts\BudgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository implements BudgetRepositoryInterface
{
    public function forUserPeriod(int $userId, int $bulan, int $tahun): Collection
    {
        return Budget::with('category')
            ->where('user_id', $userId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();
    }

    public function findForUser(int $id, int $userId): ?Budget
    {
        return Budget::where('user_id', $userId)->find($id);
    }

    public function upsert(array $attributes, array $values): Budget
    {
        return Budget::updateOrCreate($attributes, $values);
    }

    public function delete(Budget $budget): bool
    {
        return (bool) $budget->delete();
    }
}
