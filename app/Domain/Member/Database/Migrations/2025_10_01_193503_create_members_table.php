<?php

use App\Models\GeoRegion;
use App\Models\User;
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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->foreignId('region_id')->nullable()->constrained((new GeoRegion)->getTable());
            $table->boolean('is_active')->default(true);
            $table->boolean('is_banned')->default(false);
            $table->integer('following_count')->default('0');
            $table->integer('followers_count')->default('0');
            $table->integer('posts_count')->default('0');
            $table->integer('posts_votes_up_count')->default('0');
            $table->integer('posts_votes_down_count')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
