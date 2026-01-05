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

        Schema::create('cms_legacy_field_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cms_legacy_article_mapping_id');
            $table->string('field_name', 50);
            $table->text('override_value')->nullable();
            $table->enum('field_type', ['string', 'text', 'integer', 'boolean', 'json', 'datetime'])
                  ->default('string');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['cms_legacy_article_mapping_id', 'field_name'], 'idx_mapping_field');
            $table->index('is_active', 'idx_active');
            
            // Unique constraint
            $table->unique(['cms_legacy_article_mapping_id', 'field_name'], 'unique_field_override');
            
            // Foreign key
            $table->foreign('cms_legacy_article_mapping_id', 'fk_field_override_mapping')
                  ->references('id')
                  ->on('cms_legacy_article_mappings')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_legacy_field_overrides');
    }
};