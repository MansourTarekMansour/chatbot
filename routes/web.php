<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatBotController;

// Home route
Route::get('/', function () {
    return view('home');
})->name('home');

// ChatBot endpoint to handle sending chat
Route::post('/send', [ChatBotController::class, 'sendChat'])->name('chatbot.send');

// Chat routes
Route::get('/chats', [ChatController::class, 'index'])->name('chats.index'); // Get all chats
Route::post('/chats', [ChatController::class, 'store'])->name('chats.store'); // Create a new chat
Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show'); // Get messages of a chat
Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage'])->name('chats.messages.send'); // Send a message
