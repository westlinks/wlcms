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

        Schema::create('cms_legacy_article_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cms_content_item_id');
            $table->unsignedBigInteger('legacy_article_id');
            $table->enum('mapping_type', ['replacement', 'supplement', 'redirect', 'migration'])
                  ->default('replacement');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('cms_content_item_id', 'idx_cms_content');
            $table->index('legacy_article_id', 'idx_legacy_article');
            $table->index(['is_active', 'mapping_type'], 'idx_active_type');
            
            // Unique constraint
            $table->unique(['cms_content_item_id', 'legacy_article_id'], 'unique_mapping');
            
            // Foreign key
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
        Schema::dropIfExists('cms_legacy_article_mappings');
    }
};