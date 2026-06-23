<?php
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [Authcontroller::class, 'login']);

Route::middleware('/auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return new \App\Http\Resources\UserResource($request->user());
    });
});
