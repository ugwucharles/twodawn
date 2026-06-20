<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_token_id')->constrained('host_tokens')->cascadeOnDelete();
            $table->unsignedSmallInteger('count')->default(1); // number checked in in this action
            $table->string('source')->nullable(); // camera/manual/image
            $table->timestamps();
            $table->index(['order_id','host_token_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_checkins');
    }
};
