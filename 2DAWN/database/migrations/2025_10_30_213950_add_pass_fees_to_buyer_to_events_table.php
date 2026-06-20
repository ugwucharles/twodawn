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
        if (!Schema::hasColumn('events', 'pass_fees_to_buyer')) {
            Schema::table('events', function (Blueprint $table) {
                $table->boolean('pass_fees_to_buyer')->default(false)->after('is_published');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'pass_fees_to_buyer')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('pass_fees_to_buyer');
            });
        }
    }
};
