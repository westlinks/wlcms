<?php

use Illuminate\Support\Facades\Route;
use Westlinks\Wlcms\Http\Controllers\Admin\ContentController;
use Westlinks\Wlcms\Http\Controllers\Admin\MediaController;
use Westlinks\Wlcms\Http\Controllers\Admin\DashboardController;
use Westlinks\Wlcms\Http\Controllers\Admin\LegacyController;

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
            
            // Navigation management
            Route::get('legacy/navigation', [LegacyController::class, 'navigation'])->name('legacy.navigation.index');
            
            // Migration tools
            Route::get('legacy/migration', [LegacyController::class, 'migration'])->name('legacy.migration.index');
        }
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