<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function availableForUser(int $userId, ?string $tipe = null): Collection
    {
        $query = Category::where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        });

        if ($tipe) {
            $query->where('tipe', $tipe);
        }

        return $query->orderBy('nama')->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }
}
