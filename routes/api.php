<?php

use App\Http\Controllers\Api;
use App\Http\Middleware\AuthByToken;
use Illuminate\Support\Facades\Route;

Route::post('/login', [Api\UserController::class, 'login']);
Route::post('/register', [Api\UserController::class, 'register']);

Route::group(['middleware' => [AuthByToken::class]], function() {
    Route::get('/get-user', [Api\UserController::class, 'getUser']);
    Route::get('/fetch-lobby', [Api\LobbyController::class, 'fetchLobby']);
});
