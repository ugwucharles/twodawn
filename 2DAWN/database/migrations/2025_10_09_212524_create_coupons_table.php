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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent','fixed']);
            $table->unsignedInteger('value'); // percent as whole number (e.g., 10 for 10%), fixed in kobo
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
