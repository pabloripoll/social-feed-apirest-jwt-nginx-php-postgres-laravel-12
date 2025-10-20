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
        Schema::create('posts_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->foreignId('post_id')->constrained((new Post)->getTable());
            $table->boolean('up')->default(false);
            $table->boolean('down')->default(false);
            $table->integer('refresh_count')->default('0');
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_votes');
    }
};
