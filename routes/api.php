<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SavingsGoalController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// ==== Auth (publik) ====
// Rate limit 5x/menit untuk mencegah brute-force pada endpoint sensitif
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login-pin', [AuthController::class, 'loginWithPin']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
});

// ==== Butuh login (Sanctum token) ====
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard / Beranda
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Transaksi
    Route::apiResource('transactions', TransactionController::class);

    // Kategori
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Anggaran / Budget bulanan
    Route::get('/budgets', [BudgetController::class, 'index']);
    Route::post('/budgets', [BudgetController::class, 'store']);
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy']);

    // Tabungan / Savings Goals
    Route::apiResource('savings-goals', SavingsGoalController::class)->except(['destroy']);
    Route::delete('/savings-goals/{savingsGoal}', [SavingsGoalController::class, 'destroy']);
    Route::post('/savings-goals/{savingsGoal}/setoran', [SavingsGoalController::class, 'setoran']);

    // Laporan
    Route::get('/reports/summary', [ReportController::class, 'summary']);

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // Profil
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
    Route::put('/profile/pin', [ProfileController::class, 'changePin']);

    // Pengaturan
    Route::get('/settings', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);
});
