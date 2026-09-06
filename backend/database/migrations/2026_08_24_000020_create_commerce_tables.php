<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', fn (Blueprint $t) => [$t->id(), $t->foreignId('user_id')->constrained()->cascadeOnDelete(), $t->string('title')->default('آدرس من'), $t->string('receiver_name'), $t->string('receiver_mobile', 11), $t->string('province'), $t->string('city'), $t->text('address'), $t->string('postal_code', 10), $t->decimal('latitude', 10, 7)->nullable(), $t->decimal('longitude', 10, 7)->nullable(), $t->boolean('is_default')->default(false), $t->timestamps()]);
        Schema::create('shipping_methods', fn (Blueprint $t) => [$t->id(), $t->string('title'), $t->string('code')->unique(), $t->text('description')->nullable(), $t->unsignedBigInteger('base_cost')->default(0), $t->unsignedBigInteger('free_threshold')->nullable(), $t->json('rules')->nullable(), $t->boolean('is_active')->default(true), $t->timestamps()]);
        Schema::create('delivery_slots', fn (Blueprint $t) => [$t->id(), $t->date('date')->index(), $t->time('starts_at'), $t->time('ends_at'), $t->unsignedInteger('capacity'), $t->unsignedInteger('reserved')->default(0), $t->unsignedBigInteger('extra_cost')->default(0), $t->boolean('is_active')->default(true), $t->timestamps()]);
        Schema::create('discount_codes', fn (Blueprint $t) => [$t->id(), $t->string('code')->unique(), $t->string('title'), $t->string('type', 10), $t->unsignedBigInteger('value'), $t->unsignedBigInteger('max_discount')->nullable(), $t->unsignedBigInteger('min_order_amount')->default(0), $t->unsignedInteger('usage_limit')->nullable(), $t->unsignedInteger('per_user_limit')->default(1), $t->unsignedInteger('used_count')->default(0), $t->timestamp('starts_at')->nullable(), $t->timestamp('expires_at')->nullable(), $t->boolean('is_active')->default(true), $t->json('conditions')->nullable(), $t->timestamps()]);
        Schema::create('orders', fn (Blueprint $t) => [$t->id(), $t->uuid('number')->unique(), $t->foreignId('user_id')->constrained()->restrictOnDelete(), $t->foreignId('address_id')->nullable()->constrained()->nullOnDelete(), $t->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete(), $t->foreignId('delivery_slot_id')->nullable()->constrained()->nullOnDelete(), $t->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete(), $t->string('status', 30)->default('pending')->index(), $t->string('payment_status', 20)->default('unpaid'), $t->unsignedBigInteger('subtotal'), $t->unsignedBigInteger('discount_amount')->default(0), $t->unsignedBigInteger('shipping_amount')->default(0), $t->unsignedBigInteger('payable_amount'), $t->json('address_snapshot'), $t->text('customer_note')->nullable(), $t->timestamps()]);
        Schema::create('order_items', fn (Blueprint $t) => [$t->id(), $t->foreignId('order_id')->constrained()->cascadeOnDelete(), $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete(), $t->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete(), $t->string('title'), $t->string('sku'), $t->json('attributes')->nullable(), $t->unsignedBigInteger('unit_price'), $t->unsignedInteger('quantity'), $t->unsignedBigInteger('total'), $t->timestamps()]);
        Schema::create('payments', fn (Blueprint $t) => [$t->id(), $t->foreignId('order_id')->constrained()->cascadeOnDelete(), $t->string('gateway', 30), $t->string('authority')->nullable()->index(), $t->string('reference_id')->nullable(), $t->unsignedBigInteger('amount'), $t->string('status', 20)->default('pending'), $t->json('request_payload')->nullable(), $t->json('response_payload')->nullable(), $t->timestamp('paid_at')->nullable(), $t->timestamps()]);
    }

    public function down(): void
    {
        foreach (['payments', 'order_items', 'orders', 'discount_codes', 'delivery_slots', 'shipping_methods', 'addresses'] as $x) {
            Schema::dropIfExists($x);
        }
    }
};
