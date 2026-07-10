<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Tambahkan ini — supaya endpoint API yang butuh login selalu balas JSON rapi
Route::get('/login', function () {
    return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
})->name('login');