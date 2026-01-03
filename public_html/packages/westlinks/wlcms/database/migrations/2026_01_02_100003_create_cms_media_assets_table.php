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
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('disk', 20)->default('public');
            $table->string('mime_type', 100);
            $table->integer('filesize'); // bytes
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->string('caption')->nullable();
            $table->string('credit')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('cms_media_folders')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            // Indexes for performance
            $table->index('mime_type');
            $table->index('folder_id');
            $table->index('uploaded_by');
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