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
            $table->unsignedBigInteger('content_id');
            $table->string('title');
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->json('meta')->nullable();
            $table->integer('revision_number');
            $table->text('change_summary')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Optional user integration
            $table->string('created_by')->nullable(); // Fallback attribution
            $table->timestamps();

            // Indexes
            $table->index(['content_id', 'revision_number']);
            $table->index('user_id');

            // Foreign keys
            $table->foreign('content_id')->references('id')->on('cms_content_items')->onDelete('cascade');
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