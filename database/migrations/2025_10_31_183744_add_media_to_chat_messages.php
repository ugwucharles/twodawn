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
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'media_path')) {
                $table->string('media_path')->nullable()->after('body');
            }
            if (!Schema::hasColumn('chat_messages', 'media_mime')) {
                $table->string('media_mime')->nullable()->after('media_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'media_mime')) {
                $table->dropColumn('media_mime');
            }
            if (Schema::hasColumn('chat_messages', 'media_path')) {
                $table->dropColumn('media_path');
            }
        });
    }
};
