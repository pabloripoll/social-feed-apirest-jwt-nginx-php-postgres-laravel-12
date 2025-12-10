<?php

use App\Domain\Feed\Models\FeedCategory;
use App\Domain\Geo\Models\GeoRegion;
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
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid')->unique();
            $table->foreignId('user_id')->constrained((new User)->getTable());
            $table->foreignId('region_id')->nullable()->constrained((new GeoRegion)->getTable());
            $table->foreignId('category_id')->nullable()->constrained((new FeedCategory)->getTable());
            $table->boolean('is_sketch')->default(false);
            $table->boolean('is_draft')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->integer('reports_count')->default('0');
            $table->integer('thumbs_up_count')->default('0');
            $table->integer('thumbs_down_count')->default('0');
            $table->integer('favorites_count')->default('0');
            $table->string('title', 128)->nullable();
            $table->string('slug', 128)->nullable();
            $table->string('summary', 256)->nullable();
            $table->longText('article')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_posts');
    }
};
