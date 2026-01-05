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
        Schema::create('cms_legacy_migration_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique();
            $table->string('type')->index(); // batch_migration, single_item, validation, etc.
            $table->enum('status', ['running', 'completed', 'failed', 'cancelled'])->default('running');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('options')->nullable(); // Migration options and configuration
            $table->json('progress')->nullable(); // Progress tracking data
            $table->json('stats')->nullable(); // Performance and timing statistics
            $table->integer('error_count')->default(0);
            $table->integer('warning_count')->default(0);
            $table->text('notes')->nullable(); // Additional notes or comments
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['status', 'started_at']);
            $table->index(['type', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_legacy_migration_jobs');
    }
};