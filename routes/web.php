<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ContactRoleController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\InvoiceController;
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

    // ================= PROFILE =================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ================= ENTITIES =================

    // Página Inertia
    Route::get('/entities', [EntityController::class, 'page'])
        ->name('entities.page');

    Route::get('/clientes', [EntityController::class, 'clientsPage'])->name('clients');
    Route::get('/fornecedores', [EntityController::class, 'suppliersPage'])->name('suppliers');

    // JSON (Axios)
    Route::prefix('entities')->group(function () {
        Route::get('/list', [EntityController::class, 'index'])
            ->name('entities.list');

        Route::post('/', [EntityController::class, 'store'])
            ->name('entities.store');

        Route::get('{entity}', [EntityController::class, 'show'])
            ->name('entities.show');

        Route::put('{entity}', [EntityController::class, 'update'])
            ->name('entities.update');

        Route::delete('{entity}', [EntityController::class, 'destroy'])
            ->name('entities.destroy');
    });

    Route::patch('/entities/{entity}/toggle-status', [EntityController::class, 'toggleStatus'])
        ->name('entities.toggleStatus');

    // ================= CONTACTS =================

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

    // ================= SETTINGS =================

    Route::prefix('settings')->group(function () {

        // -------- Countries --------
        Route::get('/countries', [CountryController::class, 'page'])
            ->name('settings.countries.page');

        Route::get('/countries/list', [CountryController::class, 'index'])
            ->name('settings.countries.list');

        Route::post('/countries', [CountryController::class, 'store'])
            ->name('settings.countries.store');

        Route::put('/countries/{country}', [CountryController::class, 'update'])
            ->name('settings.countries.update');

        Route::delete('/countries/{country}', [CountryController::class, 'destroy'])
            ->name('settings.countries.destroy');

        // -------- Contact Roles --------
        Route::get('/contact-roles', [ContactRoleController::class, 'page'])
            ->name('settings.contact-roles.page');

        Route::get('/contact-roles/list', [ContactRoleController::class, 'index'])
            ->name('settings.contact-roles.list');

        Route::post('/contact-roles', [ContactRoleController::class, 'store'])
            ->name('settings.contact-roles.store');

        Route::put('/contact-roles/{contactRole}', [ContactRoleController::class, 'update'])
            ->name('settings.contact-roles.update');

        Route::delete('/contact-roles/{contactRole}', [ContactRoleController::class, 'destroy'])
            ->name('settings.contact-roles.destroy');
    });

    // ================= ARTICLES =================

    Route::get('/artigos', [ArticleController::class, 'page'])
        ->name('articles.page');

    Route::prefix('articles')->group(function () {
        Route::get('/list', [ArticleController::class, 'index'])
            ->name('articles.list');

        Route::post('/', [ArticleController::class, 'store'])
            ->name('articles.store');

        Route::put('{article}', [ArticleController::class, 'update'])
            ->name('articles.update');

        Route::patch('{article}/toggle-status', [ArticleController::class, 'toggleStatus'])
            ->name('articles.toggleStatus');
    });

    // ================= PROPOSALS =================

    // Página Inertia (lista)
    Route::get('/propostas', [ProposalController::class, 'page'])
        ->name('proposals.page');

    // Página Inertia (criar)
    Route::get('/propostas/create', function () {
        return Inertia::render('Proposals/Create');
    })->name('proposals.create');

    // Página Inertia (editar)
    Route::get('/propostas/{proposal}/edit', [ProposalController::class, 'edit'])
        ->name('proposals.edit');

    // JSON (Axios)
    Route::prefix('proposals')->group(function () {

        Route::post('/{proposal}/lines', [ProposalController::class, 'addLine'])
            ->name('proposals.lines.store');

        Route::delete('/lines/{line}', [ProposalController::class, 'removeLine'])
            ->name('proposals.lines.destroy');

        Route::patch('/lines/{line}', [ProposalController::class, 'updateLine'])
            ->name('proposals.lines.update');

        Route::get('/list', [ProposalController::class, 'index'])
            ->name('proposals.list');

        Route::post('/', [ProposalController::class, 'store'])
            ->name('proposals.store');

        Route::post('/{proposal}/close', [ProposalController::class, 'close'])
            ->name('proposals.close');

        Route::post('/{proposal}/invoice', [ProposalController::class, 'convertToInvoice'])
            ->name('proposals.convertToInvoice');

        Route::get('/{proposal}', [ProposalController::class, 'show'])
            ->name('proposals.show');
    });

    // ================= INVOICES =================

    // Página Inertia (lista)
    Route::get('/faturas', [InvoiceController::class, 'page'])
        ->name('invoices.page');

    // Página Inertia (ver fatura)
    Route::get('/faturas/{invoice}/edit', [InvoiceController::class, 'edit'])
        ->name('invoices.edit');

    Route::get('/faturas/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.pdf');

    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendByEmail'])
        ->name('invoices.sendEmail');


    // JSON
    Route::prefix('invoices')->group(function () {
        Route::get('/list', [InvoiceController::class, 'index'])
            ->name('invoices.list');

        Route::get('/{invoice}', [InvoiceController::class, 'show'])
            ->name('invoices.show');

        Route::post('/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
            ->name('invoices.markPaid');
    });

});

require __DIR__ . '/auth.php';
