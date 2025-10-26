<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('label')->nullable(); // e.g., Gate A / John
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['event_id','active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_tokens');
    }
};
