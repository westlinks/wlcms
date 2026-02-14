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
            // Add template field after type
            $table->string('template')->default('full-width')->after('type');
            
            // Add index for template lookups
            $table->index('template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_content_items', function (Blueprint $table) {
            $table->dropIndex(['template']);
            $table->dropColumn('template');
        });
    }
};
