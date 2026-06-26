<?php
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlueprintController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (\Illuminate\Http\Request $request) {
        return new \App\Http\Resources\UserResource($request->user());
    });

    Route::get('/blueprints', [BlueprintController::class, 'index']);
    Route::post('/blueprints', [BlueprintController::class, 'store']);
    Route::get('/blueprints/{blueprint}', [BlueprintController::class, 'show']);
    Route::patch('/blueprints/{blueprint}', [BlueprintController::class, 'update']);
    Route::delete('/blueprints/{blueprint}', [BlueprintController::class, 'destroy']);

    Route::post('/raw-contents', [RawContentController::class, 'store']);
    Route::get('/raw-contents/{rawContent}', [RawContentController::class, 'show']);
});
