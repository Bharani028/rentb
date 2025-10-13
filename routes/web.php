<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PropertiesController;
use App\Http\Controllers\Admin\BookingsController;

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'send'])
    ->middleware('throttle:6,1') // rate-limit: 6 per minute
    ->name('contact.send');
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/properties', [PropertyController::class, 'browse'])->name('properties.browse');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PropertyController::class, 'index'])->name('dashboard');

    Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::patch('/properties/{property}/{status}', [PropertyController::class, 'updateStatus'])->name('properties.status');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{application}', [BookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{application}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
        Route::patch('/bookings/{application}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
        Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
        Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    });

    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
});
Route::middleware(['auth']) // adjust to your gate/middleware
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::resource('users', UsersController::class)->only(['index','show','create','store','edit','update','destroy']);
        Route::post('users/{user}/impersonate', [UsersController::class, 'impersonate'])->name('users.impersonate');
        Route::post('users/{user}/suspend', [UsersController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/restore', [UsersController::class, 'restore'])->name('users.restore');

        // Properties (Moderation + CRUD)
        Route::resource('properties', PropertiesController::class)->only(['index','show','edit','update','destroy']);
        Route::post('properties/{property}/approve', [PropertiesController::class, 'approve'])->name('properties.approve');
        Route::post('properties/{property}/reject', [PropertiesController::class, 'reject'])->name('properties.reject');

        // Bookings
        Route::resource('bookings', BookingsController::class)->only(['index','show','destroy']);
        Route::post('bookings/{booking}/cancel', [BookingsController::class, 'cancel'])->name('bookings.cancel');
    });

require __DIR__ . '/auth.php';