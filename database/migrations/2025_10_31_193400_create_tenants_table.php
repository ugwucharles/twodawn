<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('support_email')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('brand_color', 20)->nullable(); // hex or css color
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};