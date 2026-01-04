<?php

use Illuminate\Support\Facades\Route;
use Westlinks\Wlcms\Http\Controllers\Admin\ContentController;
use Westlinks\Wlcms\Http\Controllers\Admin\MediaController;
use Westlinks\Wlcms\Http\Controllers\Admin\DashboardController;

// Admin routes (protected by middleware defined in config)
Route::middleware(config('wlcms.admin.middleware', ['web', 'auth']))
    ->prefix(config('wlcms.admin.prefix', 'admin/cms'))
    ->name('wlcms.admin.')
    ->group(function () {
        
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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