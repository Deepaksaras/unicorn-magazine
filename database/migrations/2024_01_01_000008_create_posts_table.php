<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('title', 191);
            $table->string('slug', 191)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('type', 50)->default('article')->comment('article, video, podcast, gallery');
            $table->string('format', 50)->default('standard')->comment('standard, video, audio, gallery');
            $table->integer('reading_time')->nullable()->comment('in minutes');
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('1=Published, 0=Draft, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('type');
            $table->index('is_featured');
            $table->index('is_breaking');
            $table->index('is_trending');
            $table->index('published_at');
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
