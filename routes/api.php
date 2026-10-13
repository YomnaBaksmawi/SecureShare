<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentShareController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TestNotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    Route::get(
        'documents/shared-with-me',
        [DocumentController::class, 'sharedWithMe']
    );

    Route::apiResource('documents', DocumentController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->middleware('throttle:10,1');

    Route::get(
        'documents/{document}/download',
        [DocumentController::class, 'download']
    )->middleware('throttle:30,1');

    Route::post(
        'documents/{document}/shares',
        [DocumentShareController::class, 'store']
    )->middleware('throttle:20,1');

    Route::get(
        'documents/{document}/shares',
        [DocumentShareController::class, 'index']
    );

    Route::delete(
        'documents/{document}/shares/{share}',
        [DocumentShareController::class, 'destroy']
    );

    Route::post(
        'documents/{document}',
        [DocumentController::class, 'update']
    );

    Route::put(
        'documents/{document}',
        [DocumentController::class, 'update']
    );

    Route::get(
        'users/shareable',
        [AuthController::class, 'shareable']
    );

    Route::get(
        'users',
        [AuthController::class, 'index']
    );
    Route::post(
        'friend-requests',
        [FriendRequestController::class, 'store']
    )->middleware('throttle:10,1');

    Route::get(
        'friend-requests',
        [FriendRequestController::class, 'index']
    );

    Route::get(
        'friends',
        [FriendRequestController::class, 'indexFriends']
    );

    Route::delete(
        'friends/{friendship}',
        [FriendRequestController::class, 'unfriend']
    );

    Route::post(
        'test-notification',
        [TestNotificationController::class, 'send']
    );

    Route::post(
        'device-token',
        [DeviceTokenController::class, 'store']
    );
    Route::post(
        'friend-requests/{friendship}/accept',
        [FriendRequestController::class, 'accept']
    );

    Route::post(
        'friend-requests/{friendship}/reject',
        [FriendRequestController::class, 'reject']
    );
    Route::get(
        'notifications',
        [NotificationController::class, 'index']
    );

    Route::patch(
        'notifications/{notification}/read',
        [NotificationController::class, 'markAsRead']
    );

    Route::patch(
        'notifications/read-all',
        [NotificationController::class, 'markAllAsRead']
    );
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get(
            'audit-logs',
            [AuditLogController::class, 'index']
        );
        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/options', [AdminUserController::class, 'options']);

        Route::patch('users', [AdminUserController::class, 'update']
        );
        Route::delete(
            'users/{user}',
            [AdminUserController::class, 'destroy']
        );

        Route::post(
            'users/{user}/restore',
            [AdminUserController::class, 'restore']
        );

        Route::patch(
            'users/{user}/status',
            [AdminUserController::class, 'status']
        );

    });
