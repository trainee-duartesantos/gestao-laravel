<?php

use App\Http\Controllers\EntityController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')
    ->prefix('entities')
    ->group(function () {
        Route::get('/', [EntityController::class, 'index']);
        Route::post('/', [EntityController::class, 'store']);
        Route::get('{entity}', [EntityController::class, 'show']);
        Route::put('{entity}', [EntityController::class, 'update']);
        Route::delete('{entity}', [EntityController::class, 'destroy']);
});
