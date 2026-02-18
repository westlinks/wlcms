<?php

use Illuminate\Support\Facades\Route;
use Westlinks\Wlcms\Http\Controllers\Admin\ContentController;
use Westlinks\Wlcms\Http\Controllers\Admin\MediaController;
use Westlinks\Wlcms\Http\Controllers\Admin\DashboardController;
use Westlinks\Wlcms\Http\Controllers\Admin\LegacyController;
use Westlinks\Wlcms\Http\Controllers\FormController;

// Form submission route (public)
Route::post('/wlcms/forms/{form}/submit', [FormController::class, 'submit'])
    ->middleware(['web'])
    ->name('wlcms.forms.submit');

// Test Route for Template Picker (unprotected for easy access)
Route::get('/wlcms-test-template-picker', function () {
    return view('wlcms::test-template-picker');
})->name('wlcms.test.template-picker');

// Admin routes (protected by middleware defined in config)
Route::middleware(config('wlcms.admin.middleware', ['web', 'auth']))
    ->prefix(config('wlcms.admin.prefix', 'admin/cms'))
    ->name('wlcms.admin.')
    ->group(function () {
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // TEST ROUTE - Completely minimal view
        Route::get('/test-minimal', function() {
            return view('wlcms::admin.test-minimal');
        })->name('test-minimal');

        // Content management
        Route::resource('content', ContentController::class);
        Route::post('content/{content}/publish', [ContentController::class, 'publish'])->name('content.publish');
        Route::post('content/{content}/unpublish', [ContentController::class, 'unpublish'])->name('content.unpublish');
        Route::get('content/{content}/preview', [ContentController::class, 'preview'])->name('content.preview');
        Route::get('content/{content}/revisions', [ContentController::class, 'revisions'])->name('content.revisions');
        
        // Content media attachment
        Route::post('content/{content}/media/attach', [ContentController::class, 'attachMedia'])->name('content.media.attach');
        Route::post('content/{content}/media/detach', [ContentController::class, 'detachMedia'])->name('content.media.detach');

        // Media list endpoint (must be before resource route)
        Route::get('media/list', [MediaController::class, 'listJson'])->name('media.list');
        
        // Media management
        Route::resource('media', MediaController::class)->parameters(['media' => 'media']);
        Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('media.bulk-delete');
        Route::get('media/{media}/download', [MediaController::class, 'download'])->name('media.download');
        Route::get('media/{media}/serve/{size?}', [MediaController::class, 'serve'])->name('media.serve');
        
        // Media folder management
        Route::post('media/folder', [MediaController::class, 'createFolder'])->name('media.folder.store');
        Route::put('media/folder/{folder}', [MediaController::class, 'updateFolder'])->name('media.folder.update');
        Route::delete('media/folder/{folder}', [MediaController::class, 'deleteFolder'])->name('media.folder.destroy');
        
        // Legacy article integration (conditionally registered)
        if (config('wlcms.legacy.enabled', false)) {
            // Legacy dashboard
            Route::get('legacy', [LegacyController::class, 'index'])->name('legacy.index');
            
            // Article mappings
            Route::get('legacy/mappings', [LegacyController::class, 'mappings'])->name('legacy.mappings.index');
            Route::get('legacy/mappings/create', [LegacyController::class, 'createMapping'])->name('legacy.mappings.create');
            Route::post('legacy/mappings', [LegacyController::class, 'storeMapping'])->name('legacy.mappings.store');
            Route::get('legacy/mappings/{mapping}/edit', [LegacyController::class, 'editMapping'])->name('legacy.mappings.edit');
            Route::put('legacy/mappings/{mapping}', [LegacyController::class, 'updateMapping'])->name('legacy.mappings.update');
            Route::delete('legacy/mappings/{mapping}', [LegacyController::class, 'destroyMapping'])->name('legacy.mappings.destroy');
            
            // Sync operations
            Route::post('legacy/mappings/{mapping}/sync', [LegacyController::class, 'syncMapping'])->name('legacy.mappings.sync');
            Route::post('legacy/mappings/bulk-sync', [LegacyController::class, 'bulkSync'])->name('legacy.mappings.bulk-sync');
            
            // Migration Tools Routes
            Route::prefix('legacy/migration')->name('legacy.migration.')->group(function () {
                Route::get('/', [LegacyController::class, 'migrationIndex'])->name('index');
                Route::post('/bulk', [LegacyController::class, 'bulkMigrate'])->name('bulk');
                Route::post('/sync-all', [LegacyController::class, 'syncAllMappings'])->name('sync-all');
                Route::post('/retry-errors', [LegacyController::class, 'retryErrors'])->name('retry-errors');
                Route::get('/export', [LegacyController::class, 'exportMappings'])->name('export');
                Route::post('/import', [LegacyController::class, 'importMappings'])->name('import');
                Route::post('/cleanup', [LegacyController::class, 'cleanupMappings'])->name('cleanup');
                Route::get('/activity', [LegacyController::class, 'migrationActivity'])->name('activity');
            });
            
            // Navigation Management Routes
            Route::prefix('legacy/navigation')->name('legacy.navigation.')->group(function () {
                Route::get('/', [LegacyController::class, 'navigationIndex'])->name('index');
                Route::get('/create', [LegacyController::class, 'navigationCreate'])->name('create');
                Route::post('/', [LegacyController::class, 'navigationStore'])->name('store');
                Route::get('/{navigationItem}/edit', [LegacyController::class, 'navigationEdit'])->name('edit');
                Route::put('/{navigationItem}', [LegacyController::class, 'navigationUpdate'])->name('update');
                Route::delete('/{navigationItem}', [LegacyController::class, 'navigationDestroy'])->name('destroy');
                Route::post('/bulk', [LegacyController::class, 'navigationBulk'])->name('bulk');
                Route::post('/sync-all', [LegacyController::class, 'navigationSyncAll'])->name('sync-all');
                Route::post('/{navigationItem}/sync', [LegacyController::class, 'navigationSync'])->name('sync');
                Route::post('/{navigationItem}/activate', [LegacyController::class, 'navigationActivate'])->name('activate');
                Route::post('/{navigationItem}/deactivate', [LegacyController::class, 'navigationDeactivate'])->name('deactivate');
                Route::get('/export', [LegacyController::class, 'navigationExport'])->name('export');
                Route::post('/import', [LegacyController::class, 'navigationImport'])->name('import');
            });
        }
        
        // Form Submissions
        Route::get('form-submissions', [\Westlinks\Wlcms\Http\Controllers\Admin\FormSubmissionController::class, 'index'])->name('form-submissions.index');
        Route::get('form-submissions/export', [\Westlinks\Wlcms\Http\Controllers\Admin\FormSubmissionController::class, 'export'])->name('form-submissions.export');
        Route::get('form-submissions/{submission}', [\Westlinks\Wlcms\Http\Controllers\Admin\FormSubmissionController::class, 'show'])->name('form-submissions.show');
        Route::patch('form-submissions/{submission}/status', [\Westlinks\Wlcms\Http\Controllers\Admin\FormSubmissionController::class, 'updateStatus'])->name('form-submissions.status');
        Route::delete('form-submissions/{submission}', [\Westlinks\Wlcms\Http\Controllers\Admin\FormSubmissionController::class, 'destroy'])->name('form-submissions.destroy');
    });

// Frontend routes (will be registered if enabled in config)
if (config('wlcms.frontend.enabled', false)) {
    Route::middleware(['web'])
        ->prefix('cms-content')
        ->name('wlcms.frontend.')
        ->group(function () {
            // Specific CMS content routes with prefix to avoid conflicts
            Route::get('/', [\Westlinks\Wlcms\Http\Controllers\ContentController::class, 'index'])->name('index');
            Route::get('/{slug}', [\Westlinks\Wlcms\Http\Controllers\ContentController::class, 'show'])->name('show');
        });
}