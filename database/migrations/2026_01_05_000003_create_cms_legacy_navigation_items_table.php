<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only create legacy tables if legacy integration is enabled
        if (!config('wlcms.legacy.enabled', false)) {
            return;
        }

        Schema::create('cms_legacy_navigation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cms_content_item_id');
            $table->enum('navigation_context', ['main', 'footer', 'sidebar', 'breadcrumb'])
                  ->default('main');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('label')->nullable();
            $table->string('slug')->nullable();
            $table->string('css_class')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->enum('target', ['_self', '_blank', '_parent', '_top'])
                  ->default('_self');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('cms_content_item_id', 'idx_cms_content');
            $table->index('parent_id', 'idx_parent');
            $table->index(['navigation_context', 'is_active'], 'idx_navigation_context');
            $table->index('slug', 'idx_slug');
            
            // Foreign keys
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('cms_legacy_navigation_items')
                  ->onDelete('cascade');
                  
            $table->foreign('cms_content_item_id')
                  ->references('id')
                  ->on('cms_content_items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_legacy_navigation_items');
    }
};