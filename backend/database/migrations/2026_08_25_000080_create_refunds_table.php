<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('gateway', 30);
            $table->unsignedBigInteger('amount');
            $table->string('status', 30)->default('pending')->index();
            $table->string('reference_id')->nullable()->index();
            $table->json('response_payload')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('refunds'); }
};
