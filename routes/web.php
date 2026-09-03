<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Volt::route('/my-auctions', 'my-auctions')->name('my-auctions');
    Volt::route('/listings/create', 'listings.create')->name('listings.create');
});

Volt::route('/listings', 'listings.index')->name('listings.index');
Volt::route('/listings/{listing}', 'listings.detail')->name('listings.detail');

Volt::route('/', 'home')->name('home');

require __DIR__.'/auth.php';
