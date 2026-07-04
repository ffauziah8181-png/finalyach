<?php

namespace App\Repositories\Contracts;

use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Collection;

interface SavingsGoalRepositoryInterface
{
    public function allForUser(int $userId, ?string $status = null): Collection;

    public function findForUser(int $id, int $userId): ?SavingsGoal;

    public function create(array $data): SavingsGoal;

    public function update(SavingsGoal $goal, array $data): SavingsGoal;

    public function delete(SavingsGoal $goal): bool;
}
