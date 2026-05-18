<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::post(
    '/notifications/bulk',
    [NotificationController::class, 'bulk']
);

Route::get(
    '/notifications/recipient/{recipientId}',
    [NotificationController::class, 'recipientHistory']
);
