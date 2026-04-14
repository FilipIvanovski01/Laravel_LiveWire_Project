<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Buyer routes: any authenticated user is a buyer.
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::view('/', 'pages.cart.index')->name('index');
    });

    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::view('/', 'pages.checkout.index')->name('index');
    });

    Route::prefix('buyer/orders')->name('buyer.orders.')->group(function () {
        Route::view('/', 'pages.buyer.orders.index')->name('index');
        Route::view('/{order}', 'pages.buyer.orders.show')->name('show');
    });

    Route::livewire('vendor/onboarding', 'pages::vendor.onboarding')->name('vendor.onboarding');
});

Route::middleware(['auth', 'verified', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::view('/', 'pages.vendor.products.index')->name('index');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::view('/', 'pages.vendor.orders.index')->name('index');
    });
});

require __DIR__.'/settings.php';
