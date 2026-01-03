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
        Schema::create('cms_media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('path'); // Full folder path for easy querying
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('user_id')->nullable(); // Optional user integration
            $table->string('created_by')->nullable(); // Fallback attribution
            $table->timestamps();

            // Indexes
            $table->index('parent_id');
            $table->index('path');
            $table->index('user_id');
            $table->unique(['slug', 'parent_id']); // Unique slug within parent folder

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('cms_media_folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_media_folders');
    }
};