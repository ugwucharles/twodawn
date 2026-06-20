<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount'); // kobo
            $table->string('status')->default('pending'); // pending|succeeded|failed
            $table->string('provider_ref')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['order_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};