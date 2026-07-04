<?php

namespace App\Repositories\Eloquent;

use App\Models\SavingsGoal;
use App\Repositories\Contracts\SavingsGoalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SavingsGoalRepository implements SavingsGoalRepositoryInterface
{
    public function allForUser(int $userId, ?string $status = null): Collection
    {
        $query = SavingsGoal::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function findForUser(int $id, int $userId): ?SavingsGoal
    {
        return SavingsGoal::where('user_id', $userId)
            ->with(['riwayat' => fn ($q) => $q->orderByDesc('created_at')])
            ->find($id);
    }

    public function create(array $data): SavingsGoal
    {
        return SavingsGoal::create($data);
    }

    public function update(SavingsGoal $goal, array $data): SavingsGoal
    {
        $goal->update($data);

        return $goal->fresh();
    }

    public function delete(SavingsGoal $goal): bool
    {
        return (bool) $goal->delete();
    }
}
