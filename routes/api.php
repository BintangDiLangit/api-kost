<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('kost')->group(function () {
            Route::post('list', [KostController::class, 'listKost']);
            Route::post('add', [KostController::class, 'addKost']);
            Route::post('detail/{id}', [KostController::class, 'detailKost']);
            Route::delete('delete/{id}', [KostController::class, 'deleteKost']);
            Route::post('update/{id}', [KostController::class, 'updateKost']);
        });

        Route::post('list_user', [UserController::class, 'index']);

        Route::post('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});