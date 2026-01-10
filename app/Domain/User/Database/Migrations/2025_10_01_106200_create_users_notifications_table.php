<?php

use App\Domain\User\Models\User;
use App\Domain\User\Models\UserModeration;
use App\Domain\User\Models\UserNotificationType;
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
        Schema::create('users_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique();
            $table->foreignId('type_id')->constrained((new UserNotificationType)->getTable());
            $table->foreignId('receiver_id')->constrained((new User)->getTable());
            $table->foreignId('performer_id')->nullable()->constrained((new User)->getTable());
            $table->foreignId('moderation_id')->nullable()->constrained((new UserModeration)->getTable());
            $table->unsignedBigInteger('communication_id')->nullable();
            $table->integer('notify_count')->default('0');
            $table->boolean('is_opened')->default(false);
            $table->timestamp('opened_at')->nullable()->index();
            $table->timestamps();
            $table->jsonb('payload');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_notifications');
    }
};
