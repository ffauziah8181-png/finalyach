<?php

namespace App\Providers;

use App\Repositories\Contracts\BudgetRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\SavingsGoalRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\BudgetRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\SavingsGoalRepository;
use App\Repositories\Eloquent\TransactionRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Mengikat setiap Repository Interface ke implementasi Eloquent-nya.
 * Controller/Service tidak bergantung langsung ke Eloquent, cukup ke interface ini,
 * sehingga implementasi bisa diganti (mis. untuk unit testing dengan mock/fake) tanpa
 * mengubah kode yang menggunakannya.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(SavingsGoalRepositoryInterface::class, SavingsGoalRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, BudgetRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
