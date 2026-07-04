<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function availableForUser(int $userId, ?string $tipe = null): Collection;

    public function create(array $data): Category;

    public function delete(Category $category): bool;
}
