<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('carrier', 100);
            $table->string('tracking_code')->nullable()->index();
            $table->string('tracking_url')->nullable();
            $table->string('status', 30)->default('preparing')->index();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30);
            $table->string('title');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shipment_events'); Schema::dropIfExists('shipments'); }
};
