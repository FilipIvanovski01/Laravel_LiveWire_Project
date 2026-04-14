<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::market.index')->name('home');
Route::livewire('/products/{product}', 'pages::market.show')
    ->whereUlid('product')
    ->name('market.products.show');
Route::livewire('/vendors/{vendor:slug}', 'pages::vendors.show')->name('vendors.show');
Route::redirect('/dashboard', '/')->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/profile', 'pages::profile')->name('profile.edit');
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::livewire('/', 'pages::cart.index')->name('index');
    });

    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::livewire('/', 'pages::checkout.index')->name('index');
    });

    Route::prefix('buyer/orders')->name('buyer.orders.')->group(function () {
        Route::livewire('/', 'pages::buyer.orders.index')->name('index');
        Route::livewire('/{order}', 'pages::buyer.orders.show')
            ->whereUlid('order')
            ->name('show');
    });

    Route::livewire('vendor/onboarding', 'pages::vendor.onboarding')->name('vendor.onboarding');
});

Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::livewire('/', 'pages::vendor.products.index')->name('index');
        Route::livewire('/create', 'pages::vendor.products.create')->name('create');
        Route::livewire('/{product}/edit', 'pages::vendor.products.edit')
            ->whereUlid('product')
            ->name('edit');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::livewire('/', 'pages::vendor.orders.index')->name('index');
    });
});

require __DIR__.'/settings.php';
