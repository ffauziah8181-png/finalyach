<?php

namespace App\Services;

use App\Models\Budget;
use App\Repositories\Contracts\BudgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BudgetService
{
    public function __construct(
        protected BudgetRepositoryInterface $budgets
    ) {}

    public function listForPeriod(int $userId, int $bulan, int $tahun): Collection
    {
        return $this->budgets->forUserPeriod($userId, $bulan, $tahun)
            ->map(fn (Budget $b) => [
                'id' => $b->id,
                'kategori' => $b->category->nama,
                'icon' => $b->category->icon,
                'jumlah_budget' => (float) $b->jumlah_budget,
                'terpakai' => $b->terpakai,
                'sisa' => (float) $b->jumlah_budget - $b->terpakai,
            ]);
    }

    public function setBudget(int $userId, int $categoryId, float $jumlah, int $bulan, int $tahun): Budget
    {
        return $this->budgets->upsert(
            ['user_id' => $userId, 'category_id' => $categoryId, 'bulan' => $bulan, 'tahun' => $tahun],
            ['jumlah_budget' => $jumlah]
        );
    }

    public function delete(Budget $budget): bool
    {
        return $this->budgets->delete($budget);
    }
}
