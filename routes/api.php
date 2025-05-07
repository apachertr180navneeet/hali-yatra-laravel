<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
        UserController,
        ImportController,
        ContactController,
        AuthController,
        BookingController,
        PaymentTypeController,
        SettingController,
        BookingTransactionController,
    };

// Public Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/splash-screen', 'splashScreens');
    Route::get('/timezones', 'getTimeZones');
});

Route::post('/contact', [ContactController::class, 'submitContact']);

// Auth Routes
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/send-phone-otp', 'sendPhoneOtp');
    Route::post('/verify-phone-otp', 'verifyPhoneOtp');
    Route::post('/register', 'register');
    Route::post('/verify-register', 'verifyRegister');
    Route::post('/login', 'login');
    Route::post('/set-forgot-password', 'setForgotPassword');
});

// Protected Routes (JWT Middleware)
Route::middleware('jwt.verify')->group(function () {

    // Auth-related actions
    Route::controller(AuthController::class)->group(function () {
        Route::get('/user', 'getUser');
        Route::post('/refresh', 'refresh');
        Route::post('/change-password', 'changePassword');
        Route::post('/update-profile', 'updateProfile');
        Route::post('/logout', 'logout');
        Route::delete('/delete-account', 'deleteAccount');
    });

    // Booking Import
    Route::post('/import/booking/excel', [ImportController::class, 'bookingExcelImport']);

    // Booking APIs
    Route::prefix('booking')->controller(BookingController::class)->group(function () {
        Route::get('/list', 'list');
        Route::get('/viauser/list', 'bookingviauserlist');
        Route::post('/detail', 'detail');
        Route::post('/detail/user', 'detailuser');
    });

    // Payment Type APIs
    Route::prefix('payment-type')->controller(PaymentTypeController::class)->group(function () {
        Route::get('/list', 'list');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::get('/trashed', 'trashed');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
    });

    Route::prefix('setting')->controller(SettingController::class)->group(function () {
        Route::post('/get', 'get');
        Route::post('/update', 'update');
    });

    Route::prefix('booking/transiction')->controller(BookingTransactionController::class)->group(function () {
        Route::post('/update', 'storeOrUpdateExtraWeightBooking');
    });

    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::get('/list', 'list');
        Route::post('/store', 'store');
        Route::post('/get', 'get');
        Route::post('/update', 'update');
        Route::post('/delete', 'delete');
        Route::get('/trashed', 'trashed');
        Route::post('/restore', 'restore');
        Route::post('/force-delete', 'forceDelete');
        Route::post('/status', 'status');
    });
});
