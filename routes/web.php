<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\ShoppingItemController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Giriş yapmayan hiçbir sayfayı göremez: ana adres panele gider,
// 'auth' middleware'i giriş yapmamış kişiyi /login'e yollar.
Route::redirect('/', 'dashboard')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Ev üyeleri. Kim ne yapabilir: app/Policies/UserPolicy.php
    Route::resource('members', MemberController::class)->except('show');

    Route::patch('shopping-items/{shopping_item}/buy', [ShoppingItemController::class, 'buy'])->name('shopping-items.buy');
    Route::resource('shopping-items', ShoppingItemController::class)->except('show');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
