<?php

use App\Domain\Member\Models\MemberNotificationType;
use App\Domain\Post\Models\Post;
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
        Schema::create('members_moderations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained((new User)->getTable());
            $table->foreignId('type_id')->constrained((new MemberNotificationType)->getTable());
            $table->boolean('is_applied')->default(false);
            $table->timestamp('applied_until')->nullable()->index();
            $table->boolean('is_on_member')->default(false);
            $table->boolean('is_on_post')->default(false);
            $table->foreignId('member_user_id')->constrained((new User)->getTable());
            $table->foreignId('member_post_id')->nullable()->constrained((new Post)->getTable());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_moderations');
    }
};
