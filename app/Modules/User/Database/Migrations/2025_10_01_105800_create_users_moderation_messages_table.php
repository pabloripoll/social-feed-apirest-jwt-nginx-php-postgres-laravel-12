<?php

use App\Modules\User\Models\User;
use App\Modules\User\Models\UserModeration;
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
        Schema::create('users_moderation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderation_id')->constrained((new UserModeration)->getTable());
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->boolean('is_viewed')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();
            $table->string('content', 256)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_moderation_messages');
    }
};
