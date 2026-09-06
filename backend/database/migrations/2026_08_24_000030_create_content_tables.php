<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', fn (Blueprint $t) => [$t->id(), $t->string('title'), $t->string('image'), $t->string('mobile_image')->nullable(), $t->string('link')->nullable(), $t->string('position')->default('home'), $t->unsignedInteger('sort_order')->default(0), $t->boolean('is_active')->default(true), $t->timestamp('starts_at')->nullable(), $t->timestamp('ends_at')->nullable(), $t->timestamps()]);
        Schema::create('banners', fn (Blueprint $t) => [$t->id(), $t->string('title'), $t->string('image'), $t->string('link')->nullable(), $t->string('placement')->index(), $t->unsignedInteger('sort_order')->default(0), $t->boolean('is_active')->default(true), $t->timestamps()]);
        Schema::create('faqs', fn (Blueprint $t) => [$t->id(), $t->string('question'), $t->text('answer'), $t->string('group')->default('عمومی'), $t->unsignedInteger('sort_order')->default(0), $t->boolean('is_active')->default(true), $t->timestamps()]);
        Schema::create('articles', fn (Blueprint $t) => [$t->id(), $t->foreignId('author_id')->constrained('users')->restrictOnDelete(), $t->string('title'), $t->string('slug')->unique(), $t->text('excerpt')->nullable(), $t->longText('body'), $t->string('cover')->nullable(), $t->string('status', 20)->default('draft'), $t->json('tags')->nullable(), $t->json('seo')->nullable(), $t->unsignedInteger('view_count')->default(0), $t->timestamp('published_at')->nullable(), $t->timestamps(), $t->softDeletes()]);
        Schema::create('comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->nullableMorphs('commentable');
            $t->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $t->text('body');
            $t->unsignedTinyInteger('rating')->nullable();
            $t->string('status', 20)->default('pending')->index();
            $t->boolean('is_buyer')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('saved_articles', fn (Blueprint $t) => [$t->foreignId('user_id')->constrained()->cascadeOnDelete(), $t->foreignId('article_id')->constrained()->cascadeOnDelete(), $t->timestamps(), $t->primary(['user_id', 'article_id'])]);
        Schema::create('settings', fn (Blueprint $t) => [$t->id(), $t->string('group')->index(), $t->string('key')->unique(), $t->json('value')->nullable(), $t->boolean('is_public')->default(false), $t->timestamps()]);
    }

    public function down(): void
    {
        foreach (['settings', 'saved_articles', 'comments', 'articles', 'faqs', 'banners', 'sliders'] as $x) {
            Schema::dropIfExists($x);
        }
    }
};
