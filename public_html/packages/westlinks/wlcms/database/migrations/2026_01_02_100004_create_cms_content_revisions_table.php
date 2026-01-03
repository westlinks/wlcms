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
        Schema::create('cms_content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('cms_content_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('content_data'); // Store all content item fields
            $table->string('revision_note')->nullable();
            $table->boolean('is_autosave')->default(false);
            $table->timestamp('created_at');

            // Indexes for performance
            $table->index(['content_id', 'created_at']);
            $table->index(['content_id', 'is_autosave']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_content_revisions');
    }
};