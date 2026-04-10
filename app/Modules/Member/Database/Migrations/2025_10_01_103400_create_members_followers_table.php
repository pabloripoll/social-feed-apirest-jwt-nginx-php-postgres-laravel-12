<?php

use App\Modules\User\Models\User;
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
        Schema::create('members_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->foreignId('following_user_id')->constrained((new User)->getTable());
            $table->timestamps();
            $table->index(['user_id', 'following_user_id']);
            $table->index(['following_user_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_followers');
    }
};
