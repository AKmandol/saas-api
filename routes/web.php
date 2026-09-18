<?php

use Illuminate\Support\Facades\Route;

// Route::view('/login', 'auth.login')->name('login');
// Route::view('/dashboard', 'dashboard.index')->name('dashboard');
// Route::view('/customers', 'customers.index')->name('customers');
// Route::view('/users', 'users.index')->name('users');
// Route::view('/company', 'company.index')->name('company');
// Route::view('/subscription', 'subscription.index')->name('subscription');


Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
