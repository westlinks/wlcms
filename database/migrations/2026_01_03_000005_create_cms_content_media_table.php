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
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('media_id');
            $table->string('type')->default('attachment'); // featured, gallery, attachment, etc.
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Caption override, etc.
            $table->timestamps();

            // Indexes
            $table->index(['content_id', 'type']);
            $table->index('media_id');
            $table->unique(['content_id', 'media_id', 'type'], 'unique_content_media_type');

            // Foreign keys
            $table->foreign('content_id')->references('id')->on('cms_content_items')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('cms_media_assets')->onDelete('cascade');
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