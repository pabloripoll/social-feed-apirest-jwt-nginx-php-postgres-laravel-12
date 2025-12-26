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
        Schema::create('members_notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('title_single', 64)->nullable();
            $table->string('title_double', 64)->nullable();
            $table->string('title_multiple', 64)->nullable();
            $table->string('message_single', 512)->nullable();
            $table->string('message_double', 512)->nullable();
            $table->string('message_multiple', 512)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_notification_types');
    }
};
