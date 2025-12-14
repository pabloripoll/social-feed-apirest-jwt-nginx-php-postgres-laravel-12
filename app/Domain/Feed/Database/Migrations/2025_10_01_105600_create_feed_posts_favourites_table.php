<?php

use App\Domain\Feed\Models\FeedPost;
use App\Domain\User\Models\User;
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
        Schema::create('feed_posts_favourites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained((new User)->getTable());
            $table->foreignId('post_id')->constrained((new FeedPost)->getTable());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_posts_favourites');
    }
};
