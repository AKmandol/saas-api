<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

        Route::middleware([
            'auth:sanctum',
            'company',
            'throttle:api'
        ])->group(function () {

            Route::post('/logout', [AuthController::class, 'logout']);

            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware([
        'auth:sanctum',
        'company',
        'throttle:api'
    ])->group(function () {

        Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view');
        Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.update');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete');

        Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create');
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
        Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete');

        Route::get('/company', [CompanyController::class, 'show'])->middleware('permission:company.view');
        Route::put('/company', [CompanyController::class, 'update'])->middleware('permission:company.update');
        Route::patch('/company', [CompanyController::class, 'update'])->middleware('permission:company.update');
        Route::get('/subscription', [SubscriptionController::class, 'show'])->middleware('permission:subscription.view');
        Route::get('/plans', [SubscriptionController::class, 'plans'])->middleware('permission:subscription.view');
        Route::put('/subscription', [SubscriptionController::class, 'update'])->middleware('permission:subscription.update');

        Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:analytics.view');
    });
});
