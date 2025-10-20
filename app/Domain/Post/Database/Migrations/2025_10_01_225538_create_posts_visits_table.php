<?php

use App\Domain\Post\Models\Post;
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
        Schema::create('posts_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->foreignId('post_id')->constrained((new Post)->getTable());
            $table->foreignId('visitor_user_id')->nullable()->constrained((new User)->getTable());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_visits');
    }
};
