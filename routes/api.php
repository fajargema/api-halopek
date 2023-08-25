<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => ['api', 'jwt.auth']], function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('chat', [ChatController::class, 'index']);
        Route::get('chat/{id}', [ChatController::class, 'show']);
        Route::post('chat/{id}', [ChatController::class, 'send']);
    });
});
