<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\EntityController;
use App\Http\Controllers\Api\V1\GuaranteeController;
use App\Http\Controllers\Api\V1\MeasurementController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TaxConfigurationController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EntityDocumentController;
use App\Http\Controllers\Api\V1\DocumentUploadController;
use App\Http\Controllers\Api\V1\ContractDocumentController;
use App\Http\Controllers\Api\V1\ContractProgressController;
use App\Http\Controllers\Api\V1\SettingsController;



use Illuminate\Support\Facades\Route;

/*
| API Routes
|--------------------------------------------------------------------------
| Prefixo automático: /api (definido em bootstrap/app.php)
| Versão: /api/v1/...
*/

// ─── Rotas Públicas ─────────────────────────────────────────
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // CSRF cookie para SPA (Angular)
    Route::get('/sanctum/csrf-cookie', function () {
        return response()->json(['status' => 'ok']);
    })->middleware('web');
});

// ─── Rotas Protegidas ───────────────────────────────────────
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->name('api.v1.')
    ->group(function () {

        // Auth
        Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::post('/logout-all', 'logoutFromAllDevices')->name('logout-all');
            Route::get('/me', 'me')->name('me');
            Route::put('/password', 'updatePassword')->name('password');
        });

        // Users
        Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/stats', 'stats')->name('stats');
            Route::get('/department/{department}', 'byDepartment')->name('by-department');
            Route::get('/{user}', 'show')->name('show');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::post('/{user}/activate', 'activate')->name('activate');
            Route::post('/{user}/deactivate', 'deactivate')->name('deactivate');
        });

        // Roles
        Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{role}', 'show')->name('show');
            Route::put('/{role}', 'update')->name('update');
            Route::delete('/{role}', 'destroy')->name('destroy');
            Route::post('/{role}/permissions', 'assignPermissions')->name('assign-permissions');
            Route::get('/{role}/users', 'users')->name('users');
        });

        // Permissions
        Route::controller(PermissionController::class)->prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/grouped', 'grouped')->name('grouped');
            Route::post('/create-defaults', 'createDefaults')->name('create-defaults');
        });

        // ─── Dashboard ──────────────────────────────────────────────
        Route::get('/dashboard/overview', [DashboardController::class, 'overview'])
            ->name('dashboard.overview');

        // ─── Entidades ──────────────────────────────────────────────
        Route::controller(EntityController::class)
            ->prefix('entities')
            ->name('entities.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::get('/compliance/alerts', 'complianceAlerts')->name('compliance.alerts');
                Route::get('/{entity}', 'show')->name('show');
                Route::put('/{entity}', 'update')->name('update');
                Route::delete('/{entity}', 'destroy')->name('destroy');
                Route::post('/{entity}/suspend', 'suspend')->name('suspend');
                Route::post('/{entity}/reactivate', 'reactivate')->name('reactivate');
            });

        // ─── Documentos de Entidades (NOVO) ─────────────────────────
        Route::controller(EntityDocumentController::class)
            ->prefix('entities/{entity}/documents')
            ->name('entities.documents.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{document}', 'show')->name('show');
                Route::delete('/{document}', 'destroy')->name('destroy');
            });

        // ─── Upload de Documentos ───────────────────────────────────
        Route::controller(DocumentUploadController::class)
            ->prefix('documents')
            ->name('documents.')
            ->group(function () {
                Route::post('/entities/{entity}/upload', 'uploadEntityDocument')->name('entities.upload');
                Route::post('/contracts/{contract}/upload', 'uploadContractDocument')->name('contracts.upload');
                Route::post('/guarantees/{guarantee}/upload', 'uploadGuaranteeDocument')->name('guarantees.upload');
                Route::get('/{type}/{id}/download', 'download')->name('download');
            });

        // Contracts
        Route::controller(ContractController::class)->prefix('contracts')->name('contracts.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/expiring', 'expiring')->name('expiring');
            Route::get('/overdue', 'overdue')->name('overdue');
            Route::get('/{contract}', 'show')->name('show');
            Route::put('/{contract}', 'update')->name('update');
            Route::delete('/{contract}', 'destroy')->name('destroy');
            Route::post('/{contract}/submit', 'submit')->name('submit');
            Route::post('/{contract}/approve', 'approve')->name('approve');
            Route::post('/{contract}/activate', 'activate')->name('activate');
            Route::post('/{contract}/suspend', 'suspend')->name('suspend');
            Route::post('/{contract}/terminate', 'terminate')->name('terminate');
            Route::post('/{contract}/register-bna', 'registerBna')->name('register-bna');
        });

        // ─── Documentos de Contratos ────────────────────────────────
        Route::controller(ContractDocumentController::class)
            ->prefix('contracts/{contract}/documents')
            ->name('contracts.documents.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{document}', 'show')->name('show');
                Route::delete('/{document}', 'destroy')->name('destroy');
            });

        // ─── Progresso de Contratos ─────────────────────────────────
        Route::controller(ContractProgressController::class)
            ->prefix('contracts/{contract}/progress')
            ->name('contracts.progress.')
            ->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('/', 'update')->name('update');
                Route::post('/calculate', 'calculate')->name('calculate');
            });

        // Guarantees
        Route::controller(GuaranteeController::class)->prefix('guarantees')->name('guarantees.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/expiring', 'expiring')->name('expiring');
            Route::get('/expired', 'expired')->name('expired');
            Route::post('/mark-expired', 'markExpired')->name('mark-expired');
            Route::get('/contract/{contractId}/summary', 'summary')->name('contract.summary');
            Route::get('/{guarantee}', 'show')->name('show');
            Route::put('/{guarantee}', 'update')->name('update');
            Route::delete('/{guarantee}', 'destroy')->name('destroy');
            Route::post('/{guarantee}/release', 'release')->name('release');
            Route::post('/{guarantee}/execute', 'execute')->name('execute');
            Route::post('/{guarantee}/cancel', 'cancel')->name('cancel');
        });

        // Measurements
        Route::controller(MeasurementController::class)->prefix('measurements')->name('measurements.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/approved-unpaid', 'approvedUnpaid')->name('approved-unpaid');
            Route::get('/{measurement}', 'show')->name('show');
            Route::put('/{measurement}', 'update')->name('update');
            Route::delete('/{measurement}', 'destroy')->name('destroy');
            Route::post('/{measurement}/submit', 'submit')->name('submit');
            Route::post('/{measurement}/approve', 'approve')->name('approve');
            Route::post('/{measurement}/reject', 'reject')->name('reject');
        });

        // Payments
        Route::controller(PaymentController::class)->prefix('payments')->name('payments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/overdue', 'overdue')->name('overdue');
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/contract/{contractId}/summary', 'financialSummary')->name('contract.summary');
            Route::get('/{payment}', 'show')->name('show');
            Route::delete('/{payment}', 'destroy')->name('destroy');
            Route::post('/{payment}/approve', 'approve')->name('approve');
            Route::post('/{payment}/mark-as-paid', 'markAsPaid')->name('mark-as-paid');
            Route::post('/{payment}/reject', 'reject')->name('reject');
            Route::post('/{payment}/cancel', 'cancel')->name('cancel');
            Route::post('/measurements/{measurement}/create-payment', 'createFromMeasurement')->name('create-from-measurement');
        });

        // Tax Configurations
        Route::controller(TaxConfigurationController::class)->prefix('tax-configurations')->name('tax-configurations.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/current', 'current')->name('current');
            Route::get('/{taxConfiguration}', 'show')->name('show');
            Route::put('/{taxConfiguration}', 'update')->name('update');
            Route::delete('/{taxConfiguration}', 'destroy')->name('destroy');
        });


        // ─── Definições ─────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::controller(SettingsController::class)->group(function () {
                // Company
                Route::get('/company', 'getCompany')->name('company.get');
                Route::put('/company', 'updateCompany')->name('company.update');
                Route::put('/company/features', 'updateCompanyFeatures')->name('company.features');

                // Tax Configurations
                Route::get('/tax-configurations', 'getTaxConfigurations')->name('tax.index');
                Route::put('/tax-configurations/{id}', 'updateTaxConfiguration')->name('tax.update');

                // Users
                Route::get('/users', 'getUsers')->name('users.index');
                Route::post('/users', 'createUser')->name('users.store');
                Route::put('/users/{id}', 'updateUser')->name('users.update');
                Route::post('/users/{id}/toggle-status', 'toggleUserStatus')->name('users.toggle');

                // Roles
                Route::get('/roles', 'getRoles')->name('roles.index');
                Route::put('/roles/{id}', 'updateRole')->name('roles.update');
                Route::post('/', 'storeRole')->name('roles.store');
Route::delete('/{id}', 'destroyRole')->name('roles.destroy');
            });
        });
    });
