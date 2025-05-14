<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    BookingController,
    BookingTransactionController,
    ContactController,
    ImportController,
    LocationController,
    PaymentTypeController,
    SettingController,
    TicketTypeController,
    UserController,
    UserIdProofController
};

// ───── Public Routes ─────────────────────────────────────────────────────────
Route::controller(AuthController::class)->group(function () {
    Route::get('/splash-screen', 'splashScreens');
    Route::get('/timezones', 'getTimeZones');
});

Route::post('/contact', [ContactController::class, 'submitContact']);

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/send-phone-otp', 'sendPhoneOtp');
    Route::post('/verify-phone-otp', 'verifyPhoneOtp');
    Route::post('/register', 'register');
    Route::post('/verify-register', 'verifyRegister');
    Route::post('/login', 'login');
    Route::post('/set-forgot-password', 'setForgotPassword');
});

// ───── Protected Routes ──────────────────────────────────────────────────────
Route::middleware('jwt.verify')->group(function () {

    // Auth
    Route::controller(AuthController::class)->group(function () {
        Route::get('/user', 'getUser');
        Route::post('/refresh', 'refresh');
        Route::post('/change-password', 'changePassword');
        Route::post('/update-profile', 'updateProfile');
        Route::post('/logout', 'logout');
        Route::delete('/delete-account', 'deleteAccount');
    });

    // Booking
    Route::prefix('booking')->group(function () {
        Route::controller(BookingController::class)->group(function () {
            Route::get('/list', 'list');
            Route::get('/viauser/list', 'bookingviauserlist');
            Route::post('/detail', 'detail');
            Route::post('/detail/user', 'detailuser');
        });

        Route::prefix('transaction')->controller(BookingTransactionController::class)->group(function () {
            Route::post('/update', 'storeOrUpdateExtraWeightBooking');
        });

        // Import Booking
        Route::post('/import/excel', [ImportController::class, 'bookingExcelImport']);
    });

    // Locations
    Route::prefix('location')->controller(LocationController::class)->group(function () {
        Route::get('/list', 'list');
        Route::get('/locationlist', 'locationlist');
        Route::get('/trashed', 'trashed');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
        Route::post('/order', 'order');
    });

    // Payment Types
    Route::prefix('payment-type')->controller(PaymentTypeController::class)->group(function () {
        Route::get('/list', 'list');
        Route::get('/trashed', 'trashed');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
        Route::post('/order', 'order');
    });

    // Settings
    Route::prefix('setting')->controller(SettingController::class)->group(function () {
        Route::post('/get', 'get');
        Route::post('/update', 'update');
    });

    // Ticket Types
    Route::prefix('ticket-type')->controller(TicketTypeController::class)->group(function () {
        Route::get('/list', 'list');
        Route::get('/trashed', 'trashed');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
        Route::post('/order', 'order');
    });

    // User ID Proof
    Route::prefix('user-id-proof')->controller(UserIdProofController::class)->group(function () {
        Route::get('/list', 'list');
        Route::get('/trashed', 'trashed');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
        Route::post('/order', 'order');
    });

    // Users
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/list', 'list');
        Route::get('/trashed', 'trashed');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
    });
});
