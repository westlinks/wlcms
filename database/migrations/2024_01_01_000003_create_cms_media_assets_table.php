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
        Schema::create('cms_media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('filename');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime_type');
            $table->string('type'); // image, document, video, audio
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->json('metadata')->nullable(); // Dimensions, EXIF, etc.
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->json('thumbnails')->nullable(); // Generated thumbnail paths
            $table->unsignedBigInteger('user_id')->nullable(); // Optional user integration
            $table->string('uploaded_by')->nullable(); // Fallback attribution
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('mime_type');
            $table->index('folder_id');
            $table->index('user_id');
            $table->index(['disk', 'path']);

            // Foreign keys
            $table->foreign('folder_id')->references('id')->on('cms_media_folders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_media_assets');
    }
};