<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Policies\BudgetPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\SavingsGoalPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserNotificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(SavingsGoal::class, SavingsGoalPolicy::class);
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(UserNotification::class, UserNotificationPolicy::class);
    }
}
