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
        // Drop dependent table first to satisfy FK constraints in PostgreSQL/MySQL
        if (Schema::hasTable('ticket_scans')) {
            Schema::dropIfExists('ticket_scans');
        }
        Schema::dropIfExists('tickets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('qr_path')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }
};
