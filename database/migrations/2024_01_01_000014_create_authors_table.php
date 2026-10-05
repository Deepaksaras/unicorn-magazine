<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('author_name', 191);
            $table->string('slug', 191)->unique();
            $table->text('biography')->nullable();
            $table->string('avatar')->nullable();
            $table->string('designation', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('website', 191)->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('post_count')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 0=Inactive, 4=Deleted');
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('slug');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
