<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Kategori bawaan sistem (user_id null) tidak boleh dihapus siapa pun,
     * hanya kategori custom milik user sendiri yang boleh dihapus.
     */
    public function delete(User $user, Category $category): bool
    {
        return ! is_null($category->user_id) && $user->id === $category->user_id;
    }
}
