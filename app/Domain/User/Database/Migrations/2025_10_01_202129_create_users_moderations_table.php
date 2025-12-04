<?php

use App\Domain\Feed\Models\FeedPost;
use App\Domain\Member\Models\MemberNotificationType;
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
        Schema::create('users_moderations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained((new User)->getTable());
            $table->foreignId('type_id')->constrained((new MemberNotificationType)->getTable());
            $table->boolean('is_applied')->default(false);
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_on_user')->default(false);
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->boolean('is_on_feed_post')->default(false);
            $table->foreignId('feed_post_id')->nullable()->constrained((new FeedPost)->getTable());
            $table->timestamps();
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
