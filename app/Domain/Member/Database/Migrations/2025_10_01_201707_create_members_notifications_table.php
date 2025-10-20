<?php

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
        Schema::create('members_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_type_id')->constrained((new MemberNotificationType)->getTable());
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->boolean('is_opened')->default(false);
            $table->timestamp('opened_at')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
            $table->string('message', 512);
            $table->foreignId('last_member_user_id')->nullable()->constrained((new User)->getTable());
            $table->string('last_member_nickname', 32)->nullable();
            $table->text('last_member_avatar')->nullable();
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
