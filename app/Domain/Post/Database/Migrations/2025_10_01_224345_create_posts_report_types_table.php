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
        Schema::create('posts_report_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('title', 64);
            $table->string('description', 256)->nullable();
            $table->tinyInteger('level')->default('0');
            $table->tinyInteger('position')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_report_types');
    }
};
