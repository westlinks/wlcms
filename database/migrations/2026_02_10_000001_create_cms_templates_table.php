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
        Schema::create('cms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique()->comment('Unique template identifier (e.g., event-landing-page)');
            $table->string('name')->comment('Human-readable template name');
            $table->text('description')->nullable()->comment('Template description for picker UI');
            $table->string('preview_image')->nullable()->comment('Path to preview screenshot');
            $table->json('zones')->comment('Available content zones and their configuration');
            $table->json('features')->nullable()->comment('Template features (auto_activation, form_embed, etc.)');
            $table->json('settings_schema')->nullable()->comment('Dynamic settings schema for template-specific configuration');
            $table->string('view_path')->comment('Blade view path (e.g., wlcms::templates.event-landing-page)');
            $table->string('category')->default('content')->comment('Template category (landing, content, form, archive)');
            $table->string('version')->default('1.0')->comment('Template version for compatibility');
            $table->boolean('is_default')->default(false)->comment('Is this a default/system template');
            $table->boolean('active')->default(true)->comment('Is template available for selection');
            $table->integer('sort_order')->default(0)->comment('Display order in template picker');
            $table->timestamps();

            // Indexes for performance
            $table->index('identifier');
            $table->index('category');
            $table->index(['active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_templates');
    }
};
