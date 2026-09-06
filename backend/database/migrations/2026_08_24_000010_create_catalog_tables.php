<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->json('seo')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('logo')->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->json('seo')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->restrictOnDelete();
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('product_type', 20)->default('physical');
            $t->text('short_description')->nullable();
            $t->longText('description')->nullable();
            $t->string('status', 20)->default('draft')->index();
            $t->boolean('is_featured')->default(false);
            $t->unsignedInteger('view_count')->default(0);
            $t->json('seo')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('type', 20)->default('select');
            $t->boolean('is_filterable')->default(true);
            $t->boolean('is_variant')->default(false);
            $t->string('unit')->nullable();
            $t->timestamps();
        });
        Schema::create('attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->string('value');
            $t->string('label');
            $t->string('color', 20)->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['attribute_id', 'value']);
        });
        Schema::create('product_attribute_value', function (Blueprint $t) {
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $t->primary(['product_id', 'attribute_value_id']);
        });
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('sku')->unique();
            $t->string('barcode')->nullable()->unique();
            $t->unsignedBigInteger('price');
            $t->unsignedBigInteger('compare_at_price')->nullable();
            $t->unsignedInteger('stock')->default(0);
            $t->unsignedInteger('low_stock_threshold')->default(3);
            $t->unsignedInteger('weight')->nullable();
            $t->json('dimensions')->nullable();
            $t->json('attributes');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('product_media', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $t->string('path');
            $t->string('alt')->nullable();
            $t->string('type', 20)->default('image');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('price_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $t->unsignedBigInteger('price');
            $t->timestamp('recorded_at')->useCurrent();
        });
        Schema::create('favorites', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->primary(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        foreach (['favorites', 'price_histories', 'product_media', 'product_variants', 'product_attribute_value', 'attribute_values', 'attributes', 'products', 'brands', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
