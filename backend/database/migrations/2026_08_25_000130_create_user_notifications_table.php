<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('user_notifications',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('type',40)->index();$t->string('title');$t->text('message');$t->string('action_url')->nullable();$t->json('data')->nullable();$t->timestamp('read_at')->nullable()->index();$t->timestamps();});}public function down():void{Schema::dropIfExists('user_notifications');}};
