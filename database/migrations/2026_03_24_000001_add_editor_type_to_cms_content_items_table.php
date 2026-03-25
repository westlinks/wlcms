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
            $table->enum('editor_type', ['wysiwyg', 'code'])->default('wysiwyg')->after('template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_content_items', function (Blueprint $table) {
            $table->dropColumn('editor_type');
        });
    }
};
