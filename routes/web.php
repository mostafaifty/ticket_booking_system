<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\SeatController as AdminSeatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Passenger\DashboardController as PassengerDashboardController;
use App\Http\Controllers\Passenger\ProfileController as PassengerProfileController;
use App\Http\Controllers\SeatSelectionController;
use App\Http\Controllers\TrainSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Railway Ticket Booking System
|--------------------------------------------------------------------------
*/

// Public Routes & Train Search
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/trains/search', [TrainSearchController::class, 'index'])->name('trains.search');
Route::get('/schedules/{schedule}/seats', [SeatSelectionController::class, 'index'])->name('schedules.seats');

// Authenticated Seat Selection & Ticket Booking Routes
Route::post('/schedules/{schedule}/select-seat', [SeatSelectionController::class, 'select'])
    ->middleware('auth')
    ->name('schedules.seats.select');

Route::post('/schedules/{schedule}/bookings', [BookingController::class, 'store'])
    ->middleware('auth')
    ->name('bookings.store');

Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])
    ->middleware('auth')
    ->name('bookings.confirmation');

// Guest / Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated Logout Route
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Passenger Portal (Authenticated Users)
Route::middleware(['auth'])->prefix('passenger')->name('passenger.')->group(function () {
    Route::get('/dashboard', [PassengerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [PassengerProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [PassengerProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [PassengerProfileController::class, 'updatePassword'])->name('password.update');
    Route::get('/search', [TrainSearchController::class, 'index'])->name('search');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('bookings.ticket');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

// Admin Control Panel (Authenticated Admin Role Required)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('schedules', AdminScheduleController::class);

    // Train Seat Management
    Route::get('/trains/{train}/seats', [AdminSeatController::class, 'index'])->name('trains.seats.index');
    Route::post('/trains/{train}/seats', [AdminSeatController::class, 'store'])->name('trains.seats.store');
    Route::post('/trains/{train}/seats/generate', [AdminSeatController::class, 'generate'])->name('trains.seats.generate');
    Route::delete('/seats/{seat}', [AdminSeatController::class, 'destroy'])->name('seats.destroy');

    // All Bookings Management
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});
