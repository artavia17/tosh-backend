<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', [\App\Http\Controllers\StatusController::class, 'index']);

Route::get('/countries', [\App\Http\Controllers\CountryController::class, 'index']);

Route::get('/winners', [\App\Http\Controllers\WinnerController::class, 'index']);

Route::get('/prize-stats', [\App\Http\Controllers\PrizeStatsController::class, 'index']);

Route::post('/auth/sign-up', [\App\Http\Controllers\AuthController::class, 'register']);

Route::post('/auth/sign-in', [\App\Http\Controllers\AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/protected/codes', [\App\Http\Controllers\CodeController::class, 'store']);
    Route::get('/protected/account', [\App\Http\Controllers\AuthController::class, 'account']);
    Route::get('/protected/invoices/{id}', [\App\Http\Controllers\InvoiceController::class, 'show']);
});
