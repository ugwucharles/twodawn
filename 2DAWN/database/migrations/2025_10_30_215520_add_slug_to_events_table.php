<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'use_custom_slug')) {
                $table->boolean('use_custom_slug')->default(false)->after('mood');
            }
            if (!Schema::hasColumn('events', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('use_custom_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('events', 'use_custom_slug')) {
                $table->dropColumn('use_custom_slug');
            }
        });
    }
};
