<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ContactController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== ENTITIES =====

    // Página Inertia (UI)
    Route::get('/entities', [EntityController::class, 'page'])->name('entities.page');

    // JSON endpoints (para Axios) - sessão web
    Route::prefix('entities')->group(function () {
        Route::get('/list', [EntityController::class, 'index'])->name('entities.list');
        Route::post('/', [EntityController::class, 'store'])->name('entities.store');

        Route::get('{entity}', [EntityController::class, 'show'])->name('entities.show');
        Route::put('{entity}', [EntityController::class, 'update'])->name('entities.update');
        Route::delete('{entity}', [EntityController::class, 'destroy'])->name('entities.destroy');
    });
    Route::patch(
        '/entities/{entity}/toggle-status',
        [EntityController::class, 'toggleStatus']
    )->name('entities.toggleStatus');

    Route::get('/entities/{entity}/contacts', [ContactController::class, 'page'])
        ->name('entities.contacts.page');

    Route::get('/entities/{entity}/contacts/list', [ContactController::class, 'index'])
        ->name('entities.contacts.list');

    Route::post('/entities/{entity}/contacts', [ContactController::class, 'store'])
        ->name('entities.contacts.store');

    Route::put('/contacts/{contact}', [ContactController::class, 'update'])
        ->name('contacts.update');

    Route::patch('/contacts/{contact}/toggle-status', [ContactController::class, 'toggleStatus'])
        ->name('contacts.toggleStatus');

    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
    ->name('contacts.destroy');

});

require __DIR__.'/auth.php';
