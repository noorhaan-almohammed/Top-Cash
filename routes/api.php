<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::middleware('auth:api')->get('/profile', [AuthController::class, 'getProfile']);
Route::middleware('auth:api')->put('/profile', [AuthController::class, 'updateProfile']);

Route::middleware('auth:api')->post('/withdraw', [WithdrawalController::class, 'withdraw']);
Route::middleware('auth:api')->get('/getUserCoins',[UserController::class,'showUserCoins']);
