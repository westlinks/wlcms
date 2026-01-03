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
        Schema::create('cms_content_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('type')->default('page'); // page, post, article, etc.
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->json('meta')->nullable(); // SEO and custom meta data
            $table->unsignedBigInteger('parent_id')->nullable(); // For hierarchical content
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Optional user integration
            $table->string('created_by')->nullable(); // Fallback attribution
            $table->string('updated_by')->nullable(); // Fallback attribution
            $table->timestamps();

            // Indexes
            $table->index(['type', 'status']);
            $table->index('published_at');
            $table->index('parent_id');
            $table->index('user_id');
            $table->index(['slug', 'type']);

            // Foreign key (nullable for flexible user integration)
            $table->foreign('parent_id')->references('id')->on('cms_content_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_content_items');
    }
};