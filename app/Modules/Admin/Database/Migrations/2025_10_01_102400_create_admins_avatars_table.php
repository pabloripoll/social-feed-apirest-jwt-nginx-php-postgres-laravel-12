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
        Schema::create('admins_avatars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->boolean('is_selected')->default(false);
            $table->integer('position')->default('0');
            $table->string('extension', 16);
            $table->string('path', 128);
            $table->string('name', 128);
            $table->string('title', 128);
            $table->string('slug', 128);
            $table->text('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins_avatars');
    }
};
