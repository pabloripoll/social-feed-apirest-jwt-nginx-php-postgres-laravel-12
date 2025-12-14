<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->smallInteger('position')->default('0');
            $table->integer('posts_count')->default('0');
            $table->integer('posts_thumbs_up_count')->default('0');
            $table->integer('posts_thumbs_down_count')->default('0');
            $table->integer('posts_favourites_count')->default('0');
            $table->string('title', 64);
            $table->string('description', 256)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_categories');
    }
};
