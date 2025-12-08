<?php

use App\Domain\Feed\Models\FeedPost;
use App\Domain\Feed\Models\FeedReportType;
use App\Domain\User\Models\User;
use App\Domain\User\Models\UserModeration;
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
        Schema::create('feed_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained((new FeedReportType)->getTable());
            $table->foreignId('reporter_user_id')->nullable()->constrained((new User)->getTable());
            $table->string('reporter_message', 256)->nullable();
            $table->boolean('in_review')->default(false);
            $table->timestamp('in_review_since')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('moderation_id')->nullable()->constrained((new UserModeration)->getTable());
            $table->foreignId('member_user_id')->constrained((new User)->getTable());
            $table->foreignId('member_feed_post_id')->nullable()->constrained((new FeedPost)->getTable());
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_reports');
    }
};
