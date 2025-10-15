<?php

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
        Schema::create('members_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->boolean('seen_by_user')->default(false);
            $table->timestamp('seen_by_user_at')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
            $table->boolean('is_post_visit')->default(false);
            $table->boolean('is_post_vote')->default(false);
            $table->boolean('is_new_member_post')->default(false);
            $table->foreignId('last_member_id')->constrained((new User)->getTable());
            $table->string('message', 256);
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
