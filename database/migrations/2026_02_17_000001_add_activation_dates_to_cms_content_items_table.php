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
        Schema::table('cms_content_items', function (Blueprint $table) {
            $table->timestamp('activation_date')->nullable()->after('published_at');
            $table->timestamp('deactivation_date')->nullable()->after('activation_date');
            $table->boolean('auto_activate')->default(false)->after('deactivation_date');
            $table->boolean('auto_deactivate')->default(false)->after('auto_activate');
            
            // Index for efficient date-based queries
            $table->index(['activation_date', 'deactivation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_content_items', function (Blueprint $table) {
            $table->dropIndex(['activation_date', 'deactivation_date']);
            $table->dropColumn(['activation_date', 'deactivation_date', 'auto_activate', 'auto_deactivate']);
        });
    }
};
