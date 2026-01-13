<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ContactRoleController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;

use Spatie\Activitylog\Models\Activity;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ENTITIES (Clientes / Fornecedores)
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:clients.view')->group(function () {

        Route::get('/clientes', [EntityController::class, 'clientsPage'])->name('clients');
        Route::get('/fornecedores', [EntityController::class, 'suppliersPage'])->name('suppliers');

        Route::prefix('entities')->group(function () {

            Route::get('/list', [EntityController::class, 'index'])->name('entities.list');

            Route::post('/', [EntityController::class, 'store'])
                ->middleware('permission:clients.create')
                ->name('entities.store');

            Route::get('{entity}', [EntityController::class, 'show'])->name('entities.show');

            Route::put('{entity}', [EntityController::class, 'update'])
                ->middleware('permission:clients.edit')
                ->name('entities.update');

            Route::delete('{entity}', [EntityController::class, 'destroy'])
                ->middleware('permission:clients.delete')
                ->name('entities.destroy');
        });

        Route::patch('/entities/{entity}/toggle-status', [EntityController::class, 'toggleStatus'])
            ->middleware('permission:clients.edit')
            ->name('entities.toggleStatus');
    });

    /*
    |--------------------------------------------------------------------------
    | CONTACTS
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:contacts.view')->group(function () {

        Route::get('/entities/{entity}/contacts', [ContactController::class, 'page'])
            ->name('entities.contacts.page');

        Route::get('/entities/{entity}/contacts/list', [ContactController::class, 'index'])
            ->name('entities.contacts.list');

        Route::post('/entities/{entity}/contacts', [ContactController::class, 'store'])
            ->middleware('permission:contacts.create')
            ->name('entities.contacts.store');

        Route::put('/contacts/{contact}', [ContactController::class, 'update'])
            ->middleware('permission:contacts.edit')
            ->name('contacts.update');

        Route::patch('/contacts/{contact}/toggle-status', [ContactController::class, 'toggleStatus'])
            ->middleware('permission:contacts.edit')
            ->name('contacts.toggleStatus');

        Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])
            ->middleware('permission:contacts.delete')
            ->name('contacts.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')
        ->middleware('permission:settings.view')
        ->group(function () {

            // Countries
            Route::get('/countries', [CountryController::class, 'page'])->name('settings.countries.page');
            Route::get('/countries/list', [CountryController::class, 'index'])->name('settings.countries.list');

            Route::post('/countries', [CountryController::class, 'store'])
                ->middleware('permission:settings.create')
                ->name('settings.countries.store');

            Route::put('/countries/{country}', [CountryController::class, 'update'])
                ->middleware('permission:settings.edit')
                ->name('settings.countries.update');

            Route::delete('/countries/{country}', [CountryController::class, 'destroy'])
                ->middleware('permission:settings.delete')
                ->name('settings.countries.destroy');

            // Contact Roles
            Route::get('/contact-roles', [ContactRoleController::class, 'page'])->name('settings.contact-roles.page');
            Route::get('/contact-roles/list', [ContactRoleController::class, 'index'])->name('settings.contact-roles.list');

            Route::post('/contact-roles', [ContactRoleController::class, 'store'])
                ->middleware('permission:settings.create')
                ->name('settings.contact-roles.store');

            Route::put('/contact-roles/{contactRole}', [ContactRoleController::class, 'update'])
                ->middleware('permission:settings.edit')
                ->name('settings.contact-roles.update');

            Route::delete('/contact-roles/{contactRole}', [ContactRoleController::class, 'destroy'])
                ->middleware('permission:settings.delete')
                ->name('settings.contact-roles.destroy');

            // Logs
            Route::get('/logs', fn () => Inertia::render('Settings/Logs/Index'))
                ->name('settings.logs.page');

            Route::get('/logs/list', function () {
                return Activity::with('causer')
                    ->latest()
                    ->limit(200)
                    ->get()
                    ->map(fn ($log) => [
                        'date'   => $log->created_at->format('d/m/Y'),
                        'time'   => $log->created_at->format('H:i'),
                        'user'   => $log->causer?->name ?? 'Sistema',
                        'action' => $log->description,
                        'ip'     => $log->properties['ip'] ?? null,
                    ]);
            })->name('settings.logs.list');
        });

    /*
    |--------------------------------------------------------------------------
    | ARTICLES
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:articles.view')->group(function () {

        Route::get('/artigos', [ArticleController::class, 'page'])->name('articles.page');

        Route::prefix('articles')->group(function () {

            Route::get('/list', [ArticleController::class, 'index'])->name('articles.list');

            Route::post('/', [ArticleController::class, 'store'])
                ->middleware('permission:articles.create')
                ->name('articles.store');

            Route::put('{article}', [ArticleController::class, 'update'])
                ->middleware('permission:articles.edit')
                ->name('articles.update');

            Route::patch('{article}/toggle-status', [ArticleController::class, 'toggleStatus'])
                ->middleware('permission:articles.edit')
                ->name('articles.toggleStatus');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | PROPOSALS (UI PT: /propostas | API EN: /proposals)
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:proposals.view')->group(function () {

        // UI (Português)
        Route::get('/propostas', [ProposalController::class, 'page'])->name('proposals.page');

        Route::get('/propostas/create', fn () => Inertia::render('Proposals/Create'))
            ->middleware('permission:proposals.create')
            ->name('proposals.create');

        Route::get('/propostas/{proposal}/edit', [ProposalController::class, 'edit'])
            ->middleware('permission:proposals.edit')
            ->name('proposals.edit');

        // API (Inglês) -> porque o teu Edit.vue chama /proposals/...
        Route::prefix('proposals')->group(function () {

            Route::get('/list', [ProposalController::class, 'index'])
                ->name('proposals.list');

            Route::post('/', [ProposalController::class, 'store'])
                ->middleware('permission:proposals.create')
                ->name('proposals.store');

            // 🔥 FALTAVA para o reloadProposal()
            Route::get('/{proposal}', [ProposalController::class, 'show'])
                ->name('proposals.show');

            // 🔥 FALTAVA / usado pelo ArticleSelector -> addLine()
            Route::post('/{proposal}/lines', [ProposalController::class, 'addLine'])
                ->middleware('permission:proposals.edit')
                ->name('proposals.lines.store');

            Route::patch('/lines/{line}', [ProposalController::class, 'updateLine'])
                ->middleware('permission:proposals.edit')
                ->name('proposals.lines.update');

            Route::delete('/lines/{line}', [ProposalController::class, 'removeLine'])
                ->middleware('permission:proposals.edit')
                ->name('proposals.lines.destroy');

            Route::post('/{proposal}/close', [ProposalController::class, 'close'])
                ->middleware('permission:proposals.edit')
                ->name('proposals.close');

            Route::post('/{proposal}/invoice', [ProposalController::class, 'convertToInvoice'])
                ->middleware('permission:proposals.edit')
                ->name('proposals.convertToInvoice');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | INVOICES
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:invoices.view')->group(function () {

        Route::get('/faturas', [InvoiceController::class, 'page'])->name('invoices.page');
        Route::get('/faturas/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::get('/faturas/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

        Route::prefix('invoices')->group(function () {

            Route::get('/list', [InvoiceController::class, 'index'])->name('invoices.list');

            Route::post('/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
                ->middleware('permission:invoices.edit')
                ->name('invoices.markPaid');

            Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendByEmail'])
                ->middleware('permission:invoices.edit')
                ->name('invoices.sendEmail');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ACCESS MANAGEMENT (Roles / Users)
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:users.view')->group(function () {

        // Roles
        Route::get('/gestao-acessos/roles', [RoleController::class, 'page'])->name('roles.page');
        Route::get('/gestao-acessos/roles/list', [RoleController::class, 'index'])->name('roles.list');
        Route::get('/gestao-acessos/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');

        Route::post('/gestao-acessos/roles', [RoleController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('roles.store');

        Route::put('/gestao-acessos/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:users.edit')
            ->name('roles.update');

        Route::delete('/gestao-acessos/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('roles.destroy');

        // Users
        Route::get('/gestao-acessos/utilizadores', [UserController::class, 'page'])->name('users.page');
        Route::get('/gestao-acessos/utilizadores/list', [UserController::class, 'index'])->name('users.list');

        Route::post('/gestao-acessos/utilizadores', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('users.store');

        Route::put('/gestao-acessos/utilizadores/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.edit')
            ->name('users.update');

        Route::delete('/gestao-acessos/utilizadores/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('users.destroy');
    });
});

require __DIR__ . '/auth.php';
