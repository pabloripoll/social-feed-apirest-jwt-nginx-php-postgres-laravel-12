<?php

use App\Domain\Member\Models\MemberNotificationType;
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
        Schema::create('members_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique();
            $table->foreignId('notification_type_id')->constrained((new MemberNotificationType)->getTable());
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->unsignedBigInteger('communication_id')->nullable();
            $table->foreignId('moderation_id')->nullable()->constrained((new UserModeration)->getTable());
            $table->foreignId('member_user_id')->nullable()->constrained((new User)->getTable());
            $table->integer('notify_count')->default('0');
            $table->boolean('is_opened')->default(false);
            $table->timestamp('opened_at')->nullable()->index();
            $table->timestamps();
            $table->string('message', 512);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_notifications');
    }
};
