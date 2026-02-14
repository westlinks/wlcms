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
        Schema::create('cms_content_template_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')
                ->constrained('cms_content_items')
                ->cascadeOnDelete()
                ->comment('Link to content item');
            $table->json('settings')->comment('Template-specific settings (hero_background, cta_button_text, etc.)');
            $table->json('zones_data')->nullable()->comment('Content for each template zone');
            $table->timestamps();

            // Indexes
            $table->index('content_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_content_template_settings');
    }
};
