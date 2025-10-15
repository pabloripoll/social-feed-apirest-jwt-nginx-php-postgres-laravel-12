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
        Schema::create('admins_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->boolean('is_terminated')->default(false);
            $table->boolean('is_expired')->default(false);
            $table->timestamp('expires_at')->index();
            $table->integer('refresh_count')->default('0');
            $table->timestamps();
            $table->index('created_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('requests_count')->default('0');
            $table->json('payload')->nullable();
            $table->text('token')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins_access_logs');
    }
};
