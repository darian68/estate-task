<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BuildingTaskController;
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
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/buildings/{building}/tasks', [BuildingTaskController::class, 'index']);
});

