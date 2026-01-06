<?php

use App\Domain\Feed\Models\FeedPost;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserModerationCategory;
use App\Domain\User\Models\UserModerationSanction;
use App\Domain\User\Models\UserRole;
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
        Schema::create('users_moderations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->foreignId('reporter_user_id')->constrained((new User)->getTable())->onDelete('set null');
            $table->foreignId('moderator_user_id')->nullable()->constrained((new User)->getTable());
            $table->boolean('opened')->default(false);
            $table->boolean('in_review')->default(false);
            $table->timestamp('in_review_since')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('category_id')->constrained((new UserModerationCategory)->getTable());
            $table->foreignId('sanction_id')->nullable()->constrained((new UserModerationSanction)->getTable());
            $table->boolean('is_sanction_active')->default(false);
            $table->timestamp('sanction_expires_at')->nullable();
            $table->timestamps();
            $table->foreignId('feed_post_id')->nullable()->constrained((new FeedPost)->getTable());
            $table->index('sanction_expires_at');
            $table->index('created_at');
            $table->index('feed_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_moderations');
    }
};
