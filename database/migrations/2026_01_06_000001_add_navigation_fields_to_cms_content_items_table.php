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
            // Navigation fields
            $table->boolean('show_in_menu')->default(false)->after('is_featured');
            $table->string('menu_title')->nullable()->after('show_in_menu'); 
            $table->integer('menu_order')->default(0)->after('menu_title');
            $table->string('menu_location')->default('primary')->after('menu_order'); // primary, footer, sidebar
            
            // Add index for navigation queries
            $table->index(['show_in_menu', 'menu_location', 'menu_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_content_items', function (Blueprint $table) {
            $table->dropIndex(['show_in_menu', 'menu_location', 'menu_order']);
            $table->dropColumn(['show_in_menu', 'menu_title', 'menu_order', 'menu_location']);
        });
    }
};