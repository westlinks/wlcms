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
        Schema::create('cms_content_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('cms_content_items')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('cms_media_assets')->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate relationships
            $table->unique(['content_id', 'media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_content_media');
    }
};