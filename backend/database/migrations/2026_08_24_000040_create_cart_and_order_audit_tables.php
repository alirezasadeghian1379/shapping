<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up():void{
 Schema::create('cart_items',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();$t->unsignedInteger('quantity');$t->timestamps();$t->unique(['user_id','variant_id']);});
 Schema::create('discount_redemptions',function(Blueprint $t){$t->id();$t->foreignId('discount_code_id')->constrained()->restrictOnDelete();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->foreignId('order_id')->constrained()->cascadeOnDelete();$t->unsignedBigInteger('amount');$t->timestamps();$t->unique(['discount_code_id','order_id']);});
 Schema::create('order_status_histories',function(Blueprint $t){$t->id();$t->foreignId('order_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('from_status',30)->nullable();$t->string('to_status',30);$t->text('note')->nullable();$t->timestamps();});
 } public function down():void{Schema::dropIfExists('order_status_histories');Schema::dropIfExists('discount_redemptions');Schema::dropIfExists('cart_items');} };
