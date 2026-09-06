<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('cities', function (Blueprint $table) {
            $table->id(); $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('slug'); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
            $table->unique(['province_id', 'name']); $table->unique(['province_id', 'slug']);
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('receiver_mobile')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('province_id')->constrained()->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) { $table->dropConstrainedForeignId('city_id'); $table->dropConstrainedForeignId('province_id'); });
        Schema::dropIfExists('cities'); Schema::dropIfExists('provinces');
    }
};
