<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('home');
});

Route::prefix('chats')->group(function () {
    Route::get('/', [ChatController::class, 'index']);
    Route::post('/', [ChatController::class, 'store']);
    Route::delete('/{chat}', [ChatController::class, 'destroy']);
    Route::get('/{chat}', [ChatController::class, 'show']);
    Route::post('/{chat}/messages', [ChatController::class, 'sendMessage']);
});