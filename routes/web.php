<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AlertSettingsController;
use App\Http\Controllers\Admin\SystemAnnouncementsController;
use App\Http\Controllers\Backend\ActionLogController;
use App\Http\Controllers\Backend\Auth\ScreenshotGeneratorLoginController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\LocaleController;
use App\Http\Controllers\Backend\MediaController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Backend\PettyCashController;
use App\Http\Controllers\Backend\PettyCash\DebugInvoiceUploadController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\Settings\GeminiSettingsController;
use App\Http\Controllers\Backend\TermController;
use App\Http\Controllers\Backend\TranslationController;
use App\Http\Controllers\Backend\UserLoginAsController;
use App\Http\Controllers\Backend\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Admin routes.
 */
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('roles', RoleController::class);
    Route::delete('roles/delete/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');

    Route::get('/petty-cash/{ledger?}', [PettyCashController::class, 'index'])
        ->whereNumber('ledger')
        ->name('petty-cash.index');
    Route::get('/petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
    Route::post('/petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    Route::get('/petty-cash/{ledger}/edit', [PettyCashController::class, 'edit'])
        ->whereNumber('ledger')
        ->name('petty-cash.edit');
    Route::put('/petty-cash/{ledger}', [PettyCashController::class, 'update'])
        ->whereNumber('ledger')
        ->name('petty-cash.update');
    Route::get('/petty-cash/{ledger}/delete', [PettyCashController::class, 'delete'])
        ->whereNumber('ledger')
        ->name('petty-cash.delete');
    Route::delete('/petty-cash/{ledger}', [PettyCashController::class, 'destroy'])
        ->whereNumber('ledger')
        ->name('petty-cash.destroy');
    Route::get('/petty-cash/{ledger}/print', [PettyCashController::class, 'print'])
        ->whereNumber('ledger')
        ->name('petty-cash.print');
    Route::get('/petty-cash/{ledger}/charge-request', [PettyCashController::class, 'chargeRequestPage'])
        ->whereNumber('ledger')
        ->name('petty-cash.charge-request');
    Route::get('/petty-cash/{ledger}/settlement', [PettyCashController::class, 'settlementPage'])
        ->whereNumber('ledger')
        ->name('petty-cash.settlement');
    Route::get('/petty-cash/{ledger}/transactions', [PettyCashController::class, 'transactionsPage'])
        ->whereNumber('ledger')
        ->name('petty-cash.transactions');
    Route::get('/petty-cash/backups', [PettyCashController::class, 'backups'])->name('petty-cash.backups');
    Route::get('/petty-cash/backups/{filename}/download', [PettyCashController::class, 'downloadBackup'])->name('petty-cash.backup.download');
    Route::delete('/petty-cash/backups/{filename}', [PettyCashController::class, 'deleteBackup'])->name('petty-cash.backup.delete');
    Route::post('/petty-cash/module-backup', [PettyCashController::class, 'downloadModulePackage'])->name('petty-cash.module-backup');
    Route::get('/petty-cash/archives', [PettyCashController::class, 'archivesIndex'])->name('petty-cash.archives.index');
    Route::get('/petty-cash/archives/{cycle}', [PettyCashController::class, 'showArchive'])
        ->whereNumber('cycle')
        ->name('petty-cash.archives.show');
    Route::get('/petty-cash/archives/{cycle}/edit', [PettyCashController::class, 'editArchive'])
        ->whereNumber('cycle')
        ->name('petty-cash.archives.edit');
    Route::put('/petty-cash/archives/{cycle}', [PettyCashController::class, 'updateArchive'])
        ->whereNumber('cycle')
        ->name('petty-cash.archives.update');
    Route::delete('/petty-cash/archives/{cycle}', [PettyCashController::class, 'destroyArchive'])
        ->whereNumber('cycle')
        ->name('petty-cash.archives.destroy');
    Route::get('/petty-cash/{ledger}/archives/{cycle}/download', [PettyCashController::class, 'downloadArchiveReport'])
        ->whereNumber('ledger')
        ->whereNumber('cycle')
        ->name('petty-cash.archives.download');

    Route::get('/petty-cash/debug/upload', [DebugInvoiceUploadController::class, 'create'])
        ->name('petty-cash.debug.upload');
    Route::post('/petty-cash/debug/upload', [DebugInvoiceUploadController::class, 'store'])
        ->name('petty-cash.debug.upload.store');
    Route::get('/petty-cash/debug/download/{filename}', [DebugInvoiceUploadController::class, 'download'])
        ->name('petty-cash.debug.download');

    // Alert Settings & Announcements & SMS Settings Routes (Superadmin only)
    Route::middleware('role:Superadmin')->group(function () {
        Route::get('/alert-settings', function () {
            return view('admin.alert-settings.index');
        })->name('alert-settings.index');

        Route::get('/announcements', function () {
            return view('admin.announcements.index');
        })->name('announcements.index');

        Route::get('/sms-settings', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'index'])
            ->name('sms-settings.index');
        Route::post('/sms-settings/test', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'testSMS'])
            ->name('sms-settings.test');
        Route::get('/sms-settings/credit', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'getCredit'])
            ->name('sms-settings.credit');
    });

    // Permissions Routes.
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');

    // Modules Routes.
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules/toggle-status/{module}', [ModuleController::class, 'toggleStatus'])->name('modules.toggle-status');
    Route::post('/modules/upload', [ModuleController::class, 'store'])->name('modules.store');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.delete');

    // Settings Routes.
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'store'])->name('settings.store');
    
    // Smart Invoice Settings Routes - در قسمت Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/smart-invoice', [GeminiSettingsController::class, 'index'])
            ->name('smart-invoice.index');
        Route::put('/smart-invoice', [GeminiSettingsController::class, 'update'])
            ->name('smart-invoice.update');
    });

    // Translation Routes.
    Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
    Route::post('/translations', [TranslationController::class, 'update'])->name('translations.update');
    Route::post('/translations/create', [TranslationController::class, 'create'])->name('translations.create');

    // Login as & Switch back.
    Route::resource('users', UserController::class);
    Route::delete('users/delete/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::get('users/{id}/login-as', [UserLoginAsController::class, 'loginAs'])->name('users.login-as');
    Route::post('users/switch-back', [UserLoginAsController::class, 'switchBack'])->name('users.switch-back');

    // Action Log Routes.
    Route::get('/action-log', [ActionLogController::class, 'index'])->name('actionlog.index');

    // Posts/Pages Routes - Dynamic post types.
    Route::get('/posts/{postType?}', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{postType}/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts/{postType}', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{postType}/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{postType}/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{postType}/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{postType}/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::delete('/posts/{postType}/delete/bulk-delete', [PostController::class, 'bulkDelete'])->name('posts.bulk-delete');

    // Terms Routes (Categories, Tags, etc.).
    Route::get('/terms/{taxonomy}', [TermController::class, 'index'])->name('terms.index');
    Route::get('/terms/{taxonomy}/{term}/edit', [TermController::class, 'edit'])->name('terms.edit');
    Route::post('/terms/{taxonomy}', [TermController::class, 'store'])->name('terms.store');
    Route::put('/terms/{taxonomy}/{term}', [TermController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{taxonomy}/{term}', [TermController::class, 'destroy'])->name('terms.destroy');
    Route::delete('/terms/{taxonomy}/delete/bulk-delete', [TermController::class, 'bulkDelete'])->name('terms.bulk-delete');

    // Media Routes.
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/api', [MediaController::class, 'api'])->name('api');
        Route::post('/', [MediaController::class, 'store'])->name('store')->middleware('check.upload.limits');
        Route::get('/upload-limits', [MediaController::class, 'getUploadLimits'])->name('upload-limits');
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
        Route::delete('/', [MediaController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Editor Upload Route.
    Route::post('/editor/upload', [App\Http\Controllers\Backend\EditorController::class, 'upload'])->name('editor.upload');

    // AI Content Generation Routes.
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/providers', [App\Http\Controllers\Backend\AiContentController::class, 'getProviders'])->name('providers');
        Route::post('/generate-content', [App\Http\Controllers\Backend\AiContentController::class, 'generateContent'])->name('generate-content');
    });
});

/**
 * Profile routes.
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::put('/update-additional', [ProfileController::class, 'updateAdditional'])->name('update.additional');
});

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/screenshot-login/{email}', [ScreenshotGeneratorLoginController::class, 'login'])->middleware('web')->name('screenshot.login');
Route::get('/demo-preview', fn () => view('demo.preview'))->name('demo.preview');
