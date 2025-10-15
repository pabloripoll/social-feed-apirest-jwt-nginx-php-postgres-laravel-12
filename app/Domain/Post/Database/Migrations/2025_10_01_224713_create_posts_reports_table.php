<?php

use App\Domain\Member\Models\MemberModeration;
use App\Domain\Post\Models\Post;
use App\Domain\Post\Models\PostReportType;
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
        Schema::create('posts_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained((new PostReportType)->getTable());
            $table->foreignId('reporter_user_id')->nullable()->constrained((new User)->getTable());
            $table->string('reporter_message', 256)->nullable();
            $table->boolean('in_review')->default(false);
            $table->timestamp('in_review_since')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('moderation_id')->nullable()->constrained((new MemberModeration)->getTable());
            $table->foreignId('member_user_id')->constrained((new User)->getTable());
            $table->foreignId('member_post_id')->nullable()->constrained((new Post)->getTable());
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_reports');
    }
};
