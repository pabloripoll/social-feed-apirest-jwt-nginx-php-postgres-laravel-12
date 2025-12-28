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
        Schema::create('users_notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('title_single', 64)->nullable();
            $table->string('title_multiple', 64)->nullable();
            $table->string('summary_single', 512)->nullable();
            $table->string('summary_multiple', 512)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_notification_types');
    }
};
