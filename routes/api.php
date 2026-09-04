<?php

use App\Http\Controllers\Api\V1\CbtAttemptController;
use App\Http\Controllers\Api\V1\ParentAuthController;
use App\Http\Controllers\Api\V1\ParentDashboardController;
use App\Http\Controllers\Api\V1\ParentFinanceController;
use App\Http\Controllers\Api\V1\ParentMessageController;
use App\Http\Controllers\Api\V1\ParentNotificationController;
use App\Http\Controllers\Api\V1\PaystackWebhookController;
use App\Http\Controllers\Api\V1\StudentAuthController;
use App\Http\Controllers\Api\V1\StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::post('v1/webhooks/paystack', PaystackWebhookController::class)->middleware('throttle:payment-webhook');
Route::prefix('v1/cbt')->middleware('throttle:120,1')->group(function () {
    Route::post('/exams/{exam}/start', [CbtAttemptController::class, 'start'])->middleware('throttle:cbt-start');
    Route::get('/attempt', [CbtAttemptController::class, 'show']);
    Route::put('/attempt/questions/{question}', [CbtAttemptController::class, 'answer']);
    Route::post('/attempt/submit', [CbtAttemptController::class, 'submit']);
});

Route::prefix('v1/parent')->group(function () {
    Route::post('/otp/request', [ParentAuthController::class, 'requestOtp'])->middleware('throttle:parent-otp');
    Route::post('/login', [ParentAuthController::class, 'login'])->middleware('throttle:parent-login');
    Route::post('/otp/verify', [ParentAuthController::class, 'verifyOtp'])->middleware('throttle:parent-login');
    Route::middleware(['auth:sanctum', 'role:parent', 'school'])->group(function () {
        Route::post('/logout', [ParentAuthController::class, 'logout']);
        Route::put('/password', [ParentAuthController::class, 'changePassword']);
        Route::get('/dashboard', ParentDashboardController::class);
        Route::get('/calendar', [ParentDashboardController::class, 'calendar']);
        Route::get('/children/{student}/progress', [ParentDashboardController::class, 'progress']);
        Route::get('/children/{student}/attendance', [ParentDashboardController::class, 'attendance']);
        Route::get('/children/{student}/recommendations', [ParentDashboardController::class, 'recommendations']);
        Route::get('/payments', [ParentFinanceController::class, 'index']);
        Route::get('/payments/{payment}', [ParentFinanceController::class, 'show']);
        Route::get('/fee-invoices', [ParentFinanceController::class, 'invoices']);
        Route::post('/fee-invoices/{invoice}/initialize-payment', [ParentFinanceController::class, 'initialize']);
        Route::post('/fee-invoices/{invoice}/verify-payment', [ParentFinanceController::class, 'verify'])->middleware('throttle:30,1');
        Route::get('/conversations', [ParentMessageController::class, 'index']);
        Route::get('/message-contacts', [ParentMessageController::class, 'contacts']);
        Route::post('/conversations', [ParentMessageController::class, 'store']);
        Route::get('/conversations/{conversation}', [ParentMessageController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [ParentMessageController::class, 'reply']);
        Route::get('/notifications', [ParentNotificationController::class, 'index']);
        Route::patch('/notifications/read-all', [ParentNotificationController::class, 'readAll']);
        Route::patch('/notifications/{notification}/read', [ParentNotificationController::class, 'read']);
        Route::post('/devices', [ParentNotificationController::class, 'registerDevice']);
        Route::delete('/devices', [ParentNotificationController::class, 'unregisterDevice']);
    });
});

Route::prefix('v1/student')->group(function () {
    Route::post('/login', [StudentAuthController::class, 'login'])->middleware('throttle:parent-login');
    Route::middleware(['auth:sanctum', 'role:student', 'school'])->group(function () {
        Route::post('/logout', [StudentAuthController::class, 'logout']);
        Route::get('/dashboard', StudentDashboardController::class);
    });
});
